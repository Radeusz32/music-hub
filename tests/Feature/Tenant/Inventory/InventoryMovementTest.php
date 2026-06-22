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

it('renders the movements index', function (): void {
    $this->get(tenantUrl($this->tenant, '/inventory/movements'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Tenant/Inventory/Movements/Index'));
});

it('records an inbound movement and increases stock', function (): void {
    $record = InventoryRecord::factory()->create(['quantity' => 2]);

    $this->post(tenantUrl($this->tenant, '/inventory/movements'), [
        'inventory_record_id' => $record->id,
        'type' => InventoryMovementTypeEnum::In->value,
        'quantity' => 3,
    ])->assertSessionHasNoErrors();

    expect($record->fresh()->quantity)->toBe(5);

    $movement = InventoryMovement::query()->latest('id')->first();

    expect($movement->type)->toBe(InventoryMovementTypeEnum::In)
        ->and($movement->quantity_before)->toBe(2)
        ->and($movement->quantity_after)->toBe(5);
});

it('records an outbound movement and decreases stock', function (): void {
    $record = InventoryRecord::factory()->create(['quantity' => 5]);

    $this->post(tenantUrl($this->tenant, '/inventory/movements'), [
        'inventory_record_id' => $record->id,
        'type' => InventoryMovementTypeEnum::Out->value,
        'quantity' => 2,
    ])->assertSessionHasNoErrors();

    expect($record->fresh()->quantity)->toBe(3);
});

it('refuses an outbound movement that would make stock negative', function (): void {
    $record = InventoryRecord::factory()->create(['quantity' => 1]);

    $this->post(tenantUrl($this->tenant, '/inventory/movements'), [
        'inventory_record_id' => $record->id,
        'type' => InventoryMovementTypeEnum::Out->value,
        'quantity' => 5,
    ])->assertSessionHasErrors('quantity');

    expect($record->fresh()->quantity)->toBe(1)
        ->and(InventoryMovement::query()->count())->toBe(0);
});

it('rejects a non-manual movement type', function (): void {
    $record = InventoryRecord::factory()->create(['quantity' => 5]);

    $this->post(tenantUrl($this->tenant, '/inventory/movements'), [
        'inventory_record_id' => $record->id,
        'type' => InventoryMovementTypeEnum::Sale->value,
        'quantity' => 1,
    ])->assertSessionHasErrors('type');
});

it('validates that the record must exist', function (): void {
    $this->post(tenantUrl($this->tenant, '/inventory/movements'), [
        'inventory_record_id' => 999999,
        'type' => InventoryMovementTypeEnum::In->value,
        'quantity' => 1,
    ])->assertSessionHasErrors('inventory_record_id');
});

it('reverses stock when a movement is deleted', function (): void {
    $record = InventoryRecord::factory()->create(['quantity' => 5]);

    $this->post(tenantUrl($this->tenant, '/inventory/movements'), [
        'inventory_record_id' => $record->id,
        'type' => InventoryMovementTypeEnum::In->value,
        'quantity' => 3,
    ]);

    expect($record->fresh()->quantity)->toBe(8);

    $movement = InventoryMovement::query()->latest('id')->first();

    $this->delete(tenantUrl($this->tenant, "/inventory/movements/{$movement->id}"))
        ->assertSessionHasNoErrors();

    expect($record->fresh()->quantity)->toBe(5);
});
