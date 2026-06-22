<?php

declare(strict_types=1);

use App\Enums\FeatureEnum;
use App\Enums\Tenant\RoleEnum;
use App\Models\Tenant\InventoryRecord;
use Spatie\Permission\Models\Role;

it('creates and migrates a tenant database', function (): void {
    $tenant = createTenant();

    tenancy()->initialize($tenant);

    expect(tenancy()->initialized)->toBeTrue()
        ->and(Illuminate\Support\Facades\Schema::hasTable('users'))->toBeTrue()
        ->and(Illuminate\Support\Facades\Schema::hasTable('inventory_records'))->toBeTrue();
});

it('seeds roles and permissions inside the tenant', function (): void {
    createBootedTenant();

    expect(Role::query()->pluck('name')->all())
        ->toContain(RoleEnum::Owner->value, RoleEnum::Admin->value, RoleEnum::User->value);

    $owner = tenantUser(RoleEnum::Owner);

    expect($owner->hasRole(RoleEnum::Owner->value))->toBeTrue()
        ->and($owner->can('inventory-records-read'))->toBeTrue();
});

it('isolates data between tenants', function (): void {
    $first = createTenant(domain: 'first.test');
    tenancy()->initialize($first);
    InventoryRecord::factory()->count(2)->create();
    expect(InventoryRecord::query()->count())->toBe(2);
    tenancy()->end();

    $second = createTenant(domain: 'second.test');
    tenancy()->initialize($second);
    expect(InventoryRecord::query()->count())->toBe(0);
});

it('resolves a tenant route by domain', function (): void {
    $tenant = createBootedTenant(features: [FeatureEnum::Inventory]);
    $owner = tenantUser(RoleEnum::Owner);

    $response = $this->actingAs($owner)->get(tenantUrl($tenant, '/dashboard'));

    $response->assertOk();
});
