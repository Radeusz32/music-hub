<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\Tenant\RoleEnum;
use App\Enums\TenantInvitationStatusEnum;
use App\Mail\TenantProvisionedMail;
use App\Models\Central\Domain;
use App\Models\Central\Feature;
use App\Models\Central\Tenant;
use App\Models\Central\TenantInvitation;
use App\Models\Tenant\User;
use Database\Seeders\Tenant\AllPermissionsSeeder;
use Database\Seeders\Tenant\RolesSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

final class ProvisionTenantJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly TenantInvitation $invitation) {}

    public function handle(): void
    {
        $invitation = $this->invitation;

        /** @var array<string, mixed> $companyData */
        $companyData = $invitation->company_data ?? [];

        /** @var array<string, mixed> $ownerData */
        $ownerData = $invitation->owner_data ?? [];

        // Already provisioned - guard against a re-dispatched / retried job
        // creating a duplicate tenant.
        if ($invitation->status === TenantInvitationStatusEnum::Accepted) {
            return;
        }

        $baseDomain = config('app.base_domain');
        $slug = $this->uniqueSlug(
            (string) ($companyData['domain'] ?? $companyData['company_name'] ?? 'tenant'),
            (string) $baseDomain,
        );
        $domain = "{$slug}.{$baseDomain}";

        $tenant = null;

        try {
            // Create tenant - TenancyServiceProvider pipeline synchronously runs
            // CreateDatabase → MigrateDatabase → CreateTenantStorageLink on TenantCreated.
            $tenant = Tenant::create([
                'id' => Str::uuid()->toString(),
                'company_name' => $companyData['company_name'] ?? null,
                'tax_id' => $companyData['tax_id'] ?? null,
                'regon' => $companyData['regon'] ?? null,
                'krs_number' => $companyData['krs_number'] ?? null,
                'company_email' => $companyData['company_email'] ?? null,
                'company_phone' => $companyData['company_phone'] ?? null,
                'website' => $companyData['website'] ?? null,
                'street' => $companyData['street'] ?? null,
                'building_number' => $companyData['building_number'] ?? null,
                'apartment_number' => $companyData['apartment_number'] ?? null,
                'postal_code' => $companyData['postal_code'] ?? null,
                'city' => $companyData['city'] ?? null,
                'country' => $companyData['country'] ?? null,
                'is_active' => true,
            ]);

            $tenant->domains()->create([
                'domain' => $domain,
            ]);

            $tenant->features()->sync(Feature::query()->get());

            // Create owner user inside the tenant database
            tenancy()->initialize($tenant);

            try {
                (new RolesSeeder())->run();
                (new AllPermissionsSeeder())->run();

                $user = User::query()->create([
                    'first_name' => $ownerData['first_name'] ?? '',
                    'last_name' => $ownerData['last_name'] ?? '',
                    'email' => $ownerData['email'] ?? $invitation->email,
                    'phone' => $ownerData['phone'] ?? null,
                    'pesel' => $ownerData['pesel'] ?? null,
                    'password' => Hash::make((string) ($ownerData['password'] ?? Str::random(16))),
                    'is_active' => true,
                ]);

                $user->syncRoles([RoleEnum::Owner->value]);

                event(new Registered($user));
            } finally {
                tenancy()->end();
            }

            $invitation->update([
                'status' => TenantInvitationStatusEnum::Accepted,
                'tenant_id' => $tenant->id,
            ]);
        } catch (Throwable $e) {
            // Roll everything back: drop the tenant DB + central rows so a
            // partial failure never leaves an orphaned tenant behind.
            $this->rollback($tenant);

            throw $e;
        }

        // Confirmation e-mail is best-effort - a mail failure must not undo a
        // successfully provisioned tenant (nor retry the whole job).
        $this->sendWelcomeEmail($companyData, $ownerData, $domain, $invitation);
    }

    private function rollback(?Tenant $tenant): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if (! $tenant instanceof Tenant) {
            return;
        }

        try {
            $tenant->features()->detach();
            $tenant->domains()->delete();
            // TenantDeleted → DeleteDatabase (synchronous) drops the tenant DB.
            $tenant->delete();
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * @param  array<string, mixed>  $companyData
     * @param  array<string, mixed>  $ownerData
     */
    private function sendWelcomeEmail(array $companyData, array $ownerData, string $domain, TenantInvitation $invitation): void
    {
        try {
            $scheme = str_starts_with((string) config('app.url'), 'https') ? 'https' : 'http';
            $ownerName = mb_trim(((string) ($ownerData['first_name'] ?? '')).' '.((string) ($ownerData['last_name'] ?? '')));

            Mail::to($ownerData['email'] ?? $invitation->email)->send(new TenantProvisionedMail(
                companyName: (string) ($companyData['company_name'] ?? $domain),
                ownerName: $ownerName !== '' ? $ownerName : 'Właścicielu',
                loginUrl: "{$scheme}://{$domain}/login",
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function uniqueSlug(string $name, string $baseDomain): string
    {
        $base = Str::slug($name) ?: 'tenant';
        $slug = $base;
        $i = 2;

        while (Domain::query()->where('domain', "{$slug}.{$baseDomain}")->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
