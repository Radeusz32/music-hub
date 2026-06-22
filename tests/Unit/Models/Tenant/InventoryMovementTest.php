<?php

declare(strict_types=1);

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Models\Tenant\User;

beforeEach(function (): void {
    createBootedTenant();
});

it('casts the type, quantities and sale price', function (): void {
    $movement = InventoryMovement::factory()->create([
        'type' => InventoryMovementTypeEnum::In,
        'quantity' => 3,
        'quantity_before' => 2,
        'quantity_after' => 5,
        'sale_price' => '10',
    ])->refresh();

    expect($movement->type)->toBe(InventoryMovementTypeEnum::In)
        ->and($movement->quantity)->toBe(3)
        ->and($movement->quantity_before)->toBe(2)
        ->and($movement->quantity_after)->toBe(5)
        ->and($movement->sale_price)->toBe('10.00');
});

it('soft deletes', function (): void {
    $movement = InventoryMovement::factory()->create();

    $movement->delete();

    expect(InventoryMovement::query()->find($movement->id))->toBeNull()
        ->and(InventoryMovement::withTrashed()->find($movement->id))->not->toBeNull();
});

it('relates to its record and user', function (): void {
    $user = tenantUser();
    $record = InventoryRecord::factory()->create();
    $movement = InventoryMovement::factory()->create([
        'inventory_record_id' => $record->id,
        'user_id' => $user->id,
    ]);

    expect($movement->inventoryRecord)->toBeInstanceOf(InventoryRecord::class)
        ->and($movement->inventoryRecord->id)->toBe($record->id)
        ->and($movement->user)->toBeInstanceOf(User::class)
        ->and($movement->user->id)->toBe($user->id);
});
