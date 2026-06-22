<?php

declare(strict_types=1);

use App\Models\Tenant\InventoryRecord;
use App\Models\Tenant\User;

it('keeps inventory records scoped to their own tenant', function (): void {
    $alpha = createTenant(domain: 'alpha.test');
    tenancy()->initialize($alpha);
    InventoryRecord::factory()->create(['name' => 'Alpha Record']);
    tenancy()->end();

    $beta = createTenant(domain: 'beta.test');
    tenancy()->initialize($beta);

    expect(InventoryRecord::query()->count())->toBe(0);

    tenancy()->end();
    tenancy()->initialize($alpha);

    expect(InventoryRecord::query()->where('name', 'Alpha Record')->exists())->toBeTrue();
});

it('allows the same email to exist independently in different tenants', function (): void {
    $alpha = createBootedTenant(domain: 'alpha.test');
    tenantUser(attributes: ['email' => 'shared@example.test']);
    tenancy()->end();

    $beta = createBootedTenant(domain: 'beta.test');

    expect(User::query()->where('email', 'shared@example.test')->exists())->toBeFalse();

    $user = tenantUser(attributes: ['email' => 'shared@example.test']);

    expect($user->email)->toBe('shared@example.test');
});

it('serves a tenant route only on its own domain', function (): void {
    $alpha = createBootedTenant(domain: 'alpha.test');
    $beta = createBootedTenant(domain: 'beta.test');

    $alphaUser = (function () use ($alpha) {
        tenancy()->initialize($alpha);

        return tenantUser();
    })();

    $this->actingAs($alphaUser)
        ->get(tenantUrl($alpha, '/dashboard'))
        ->assertOk();
});
