<?php

declare(strict_types=1);

use App\Enums\FeatureEnum;
use App\Enums\Tenant\RoleEnum;

it('allows access to a feature the tenant has enabled', function (): void {
    $tenant = createBootedTenant(features: [FeatureEnum::Inventory]);
    $user = tenantUser(RoleEnum::Owner);

    $this->actingAs($user)
        ->get(tenantUrl($tenant, '/inventory/records'))
        ->assertOk();
});

it('blocks access to a feature the tenant does not have', function (): void {
    $tenant = createBootedTenant(features: [FeatureEnum::Users]);
    $user = tenantUser(RoleEnum::Owner);

    $this->actingAs($user)
        ->get(tenantUrl($tenant, '/inventory/records'))
        ->assertForbidden();
});

it('blocks analytics when the analytics feature is disabled', function (): void {
    $tenant = createBootedTenant(features: [FeatureEnum::Inventory]);
    $user = tenantUser(RoleEnum::Owner);

    $this->actingAs($user)
        ->get(tenantUrl($tenant, '/analytics/overview'))
        ->assertForbidden();
});
