<?php

declare(strict_types=1);

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Enums\Tenant\RoleEnum;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = createBootedTenant();
    $this->owner = tenantUser(RoleEnum::Owner);
    $this->actingAs($this->owner);
});

it('renders the sales index', function (): void {
    $this->get(tenantUrl($this->tenant, '/inventory/sales'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Tenant/Inventory/Sales/Index'));
});

it('records a sale, decreases stock and stores the sale price', function (): void {
    $record = InventoryRecord::factory()->create(['quantity' => 4]);

    $this->post(tenantUrl($this->tenant, '/inventory/sales'), [
        'inventory_record_id' => $record->id,
        'quantity' => 2,
        'sale_price' => '149.99',
    ])->assertSessionHasNoErrors();

    expect($record->fresh()->quantity)->toBe(2);

    $movement = InventoryMovement::query()->latest('id')->first();

    expect($movement->type)->toBe(InventoryMovementTypeEnum::Sale)
        ->and($movement->quantity)->toBe(2)
        ->and((float) $movement->sale_price)->toBe(149.99);
});

it('refuses a sale that exceeds available stock', function (): void {
    $record = InventoryRecord::factory()->create(['quantity' => 1]);

    $this->post(tenantUrl($this->tenant, '/inventory/sales'), [
        'inventory_record_id' => $record->id,
        'quantity' => 3,
        'sale_price' => '50.00',
    ])->assertSessionHasErrors('quantity');

    expect($record->fresh()->quantity)->toBe(1)
        ->and(InventoryMovement::query()->count())->toBe(0);
});

it('validates required sale fields', function (): void {
    $this->post(tenantUrl($this->tenant, '/inventory/sales'), [])
        ->assertSessionHasErrors(['inventory_record_id', 'quantity', 'sale_price']);
});
