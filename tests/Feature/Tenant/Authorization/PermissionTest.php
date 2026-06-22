<?php

declare(strict_types=1);

use App\Enums\Tenant\RoleEnum;
use App\Models\Tenant\InventoryRecord;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->tenant = createBootedTenant();
});

it('allows a user with the required permission to read inventory', function (): void {
    $user = tenantUser(RoleEnum::User);

    $this->actingAs($user)
        ->get(tenantUrl($this->tenant, '/inventory/records'))
        ->assertOk();
});

it('forbids an action when the role lacks the permission', function (): void {
    Role::findByName(RoleEnum::User->value, 'web')
        ->revokePermissionTo('inventory-records-create');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $user = tenantUser(RoleEnum::User);

    $this->actingAs($user)
        ->post(tenantUrl($this->tenant, '/inventory/records'), [
            'name' => 'Nope',
            'artist' => 'Nobody',
            'genre' => 'Rock',
            'format' => 'lp',
            'condition' => 'mint',
            'quantity' => 1,
        ])
        ->assertForbidden();

    expect(InventoryRecord::query()->count())->toBe(0);
});

it('forbids reading inventory when the read permission is revoked', function (): void {
    foreach (RoleEnum::cases() as $role) {
        Role::findByName($role->value, 'web')->revokePermissionTo('inventory-records-read');
    }
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $user = tenantUser(RoleEnum::User);

    $this->actingAs($user)
        ->get(tenantUrl($this->tenant, '/inventory/records'))
        ->assertForbidden();
});
