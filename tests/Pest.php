<?php

declare(strict_types=1);

use App\Enums\FeatureEnum;
use App\Enums\Tenant\RoleEnum;
use App\Models\Central\Feature;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();

        $this->freezeTime();
    })
    ->afterEach(function (): void {
        // Tenant databases are real, separate databases created by the
        // TenantCreated job pipeline. RefreshDatabase only wraps the central
        // connection in a transaction, so tenant databases must be dropped
        // explicitly or they accumulate on the MySQL server.
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        Tenant::all()->each(fn (Tenant $tenant) => $tenant->delete());
    })
    ->in('Browser', 'Feature', 'Unit');

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Tenancy test helpers
|--------------------------------------------------------------------------
*/

/**
 * Create a tenant with a routable domain and the given features enabled.
 * The tenant's database is created and migrated synchronously by the
 * TenantCreated job pipeline (see TenancyServiceProvider).
 *
 * @param  list<FeatureEnum>  $features
 */
function createTenant(
    array $features = [FeatureEnum::Inventory, FeatureEnum::Analytics, FeatureEnum::Users, FeatureEnum::Settings],
    string $domain = 'acme.test',
    bool $active = true,
): Tenant {
    foreach (FeatureEnum::cases() as $feature) {
        Feature::firstOrCreate(['name' => $feature->value], ['label' => $feature->label()]);
    }

    $tenant = Tenant::create([
        'company_name' => 'Test Company',
        'is_active' => $active,
    ]);

    $tenant->domains()->create(['domain' => $domain]);

    if ($features !== []) {
        $names = array_map(fn (FeatureEnum $f): string => $f->value, $features);
        $tenant->features()->sync(Feature::query()->whereIn('name', $names)->pluck('id'));
    }

    return $tenant;
}

/**
 * Initialize tenancy for the given tenant and seed its roles and the full
 * permission matrix. Must be called before creating tenant users with roles.
 */
function bootTenant(Tenant $tenant): Tenant
{
    tenancy()->initialize($tenant);

    Artisan::call('db:seed', [
        '--class' => Database\Seeders\Tenant\RolesSeeder::class,
        '--force' => true,
    ]);

    Artisan::call('db:seed', [
        '--class' => Database\Seeders\Tenant\AllPermissionsSeeder::class,
        '--force' => true,
    ]);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $tenant;
}

/**
 * Create a tenant, boot it (roles + permissions), and return it with tenancy
 * already initialized for the current test.
 *
 * @param  list<FeatureEnum>  $features
 */
function createBootedTenant(
    array $features = [FeatureEnum::Inventory, FeatureEnum::Analytics, FeatureEnum::Users, FeatureEnum::Settings],
    string $domain = 'acme.test',
    bool $active = true,
): Tenant {
    return bootTenant(createTenant($features, $domain, $active));
}

/**
 * Create a tenant user with the given role. Tenancy must already be booted.
 */
function tenantUser(RoleEnum $role = RoleEnum::Owner, array $attributes = []): User
{
    $user = User::factory()->create(array_merge(['is_active' => true], $attributes));
    $user->syncRoles([$role->value]);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

/**
 * Absolute URL for a path on the tenant's first domain, so requests are
 * routed through InitializeTenancyByDomain.
 */
function tenantUrl(Tenant $tenant, string $path = '/'): string
{
    $domain = $tenant->domains()->first()->domain;

    return 'http://'.$domain.'/'.mb_ltrim($path, '/');
}
