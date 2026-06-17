<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\TenantInvitationStatusEnum;
use App\Http\Resources\Central\TenantInvitationDataTable;
use App\Jobs\ProvisionTenantJob;
use App\Mail\TenantInvitationMail;
use App\Models\Central\TenantInvitation;
use App\Services\BaseService;
use App\Transformers\Central\TenantInvitationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class InvitationService extends BaseService
{
    /**
     * @return array<string, mixed>
     */
    public function index(Request $request): array
    {
        return $this->fetchForDataTable(TenantInvitationDataTable::class, $request);
    }

    /**
     * @return array<string, mixed>
     */
    public function showData(TenantInvitation $invitation): array
    {
        return (new TenantInvitationTransformer())->toArray($invitation, request());
    }

    public function create(string $email): TenantInvitation
    {
        $invitation = TenantInvitation::query()->create([
            'uuid' => Str::uuid()->toString(),
            'email' => $email,
            'status' => TenantInvitationStatusEnum::Pending,
            'expires_at' => now()->addWeek(),
        ]);

        $url = route('invitation.show', ['uuid' => $invitation->uuid]);

        Mail::to($email)->send(new TenantInvitationMail($invitation, $url));

        return $invitation;
    }

    public function findByUuid(string $uuid): ?TenantInvitation
    {
        return TenantInvitation::query()
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $companyData
     * @param  array<string, mixed>  $ownerData
     */
    public function fill(TenantInvitation $invitation, array $companyData, array $ownerData): TenantInvitation
    {
        $invitation->update([
            'company_data' => $companyData,
            'owner_data' => $ownerData,
            'status' => TenantInvitationStatusEnum::Filled,
        ]);

        return $invitation->refresh();
    }

    public function resend(TenantInvitation $invitation): void
    {
        $url = route('invitation.show', ['uuid' => $invitation->uuid]);

        Mail::to($invitation->email)->send(new TenantInvitationMail($invitation, $url));
    }

    public function accept(TenantInvitation $invitation): void
    {
        ProvisionTenantJob::dispatch($invitation);
    }
}
