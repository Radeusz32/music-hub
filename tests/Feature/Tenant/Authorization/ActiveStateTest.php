<?php

declare(strict_types=1);

use App\Enums\Tenant\RoleEnum;

it('redirects to the inactive notice when the tenant is deactivated', function (): void {
    $tenant = createBootedTenant(active: false);
    $user = tenantUser(RoleEnum::Owner);

    $this->actingAs($user)
        ->get(tenantUrl($tenant, '/dashboard'))
        ->assertRedirectContains('/inactive');
});

it('redirects to the user-inactive notice when the user is deactivated', function (): void {
    $tenant = createBootedTenant();
    $user = tenantUser(RoleEnum::Owner, ['is_active' => false]);

    $this->actingAs($user)
        ->get(tenantUrl($tenant, '/dashboard'))
        ->assertRedirectContains('/user-inactive');
});

it('lets an active user of an active tenant through', function (): void {
    $tenant = createBootedTenant();
    $user = tenantUser(RoleEnum::Owner);

    $this->actingAs($user)
        ->get(tenantUrl($tenant, '/dashboard'))
        ->assertOk();
});
