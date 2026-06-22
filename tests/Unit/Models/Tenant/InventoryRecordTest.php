<?php

declare(strict_types=1);

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Models\Tenant\User;

beforeEach(function (): void {
    createBootedTenant();
});

it('casts format and condition to enums', function (): void {
    $record = InventoryRecord::factory()->vinyl()->mint()->create();

    expect($record->refresh()->format)->toBe(DiscFormatEnum::LP)
        ->and($record->condition)->toBe(DiscConditionEnum::Mint);
});

it('casts numeric and decimal fields', function (): void {
    $record = InventoryRecord::factory()->create([
        'quantity' => 5,
        'year' => 1999,
        'purchase_price_per_unit' => '12.5',
    ])->refresh();

    expect($record->quantity)->toBe(5)
        ->and($record->year)->toBe(1999)
        ->and($record->purchase_price_per_unit)->toBe('12.50');
});

it('soft deletes', function (): void {
    $record = InventoryRecord::factory()->create();

    $record->delete();

    expect(InventoryRecord::query()->find($record->id))->toBeNull()
        ->and(InventoryRecord::withTrashed()->find($record->id))->not->toBeNull();
});

it('zeroes the quantity in the outOfStock state', function (): void {
    expect(InventoryRecord::factory()->outOfStock()->create()->quantity)->toBe(0);
});

it('relates to its user and movements', function (): void {
    $user = tenantUser();
    $record = InventoryRecord::factory()->create(['user_id' => $user->id]);
    InventoryMovement::factory()->create(['inventory_record_id' => $record->id]);

    expect($record->user)->toBeInstanceOf(User::class)
        ->and($record->user->id)->toBe($user->id)
        ->and($record->movements)->toHaveCount(1);
});

it('registers a single-file cover media collection', function (): void {
    $record = InventoryRecord::factory()->create();

    $collection = collect($record->getRegisteredMediaCollections())
        ->firstWhere('name', 'cover');

    expect($collection)->not->toBeNull()
        ->and($collection->singleFile)->toBeTrue();
});
