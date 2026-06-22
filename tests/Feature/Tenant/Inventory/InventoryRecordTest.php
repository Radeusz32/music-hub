<?php

declare(strict_types=1);

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
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

it('renders the inventory index', function (): void {
    InventoryRecord::factory()->count(3)->create();

    $this->get(tenantUrl($this->tenant, '/inventory/records'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Tenant/Inventory/Index'));
});

it('stores a new record and logs the opening stock', function (): void {
    $payload = [
        'name' => 'Kind of Blue',
        'artist' => 'Miles Davis',
        'genre' => 'Jazz',
        'format' => DiscFormatEnum::LP->value,
        'condition' => DiscConditionEnum::Mint->value,
        'quantity' => 5,
    ];

    $response = $this->post(tenantUrl($this->tenant, '/inventory/records'), $payload);

    $response->assertSessionHasNoErrors();

    $record = InventoryRecord::query()->firstWhere('name', 'Kind of Blue');

    expect($record)->not->toBeNull()
        ->and($record->quantity)->toBe(5)
        ->and($record->user_id)->toBe($this->owner->id);

    expect(InventoryMovement::query()
        ->where('inventory_record_id', $record->id)
        ->where('type', InventoryMovementTypeEnum::Initial)
        ->where('quantity', 5)
        ->exists())->toBeTrue();
});

it('does not log an opening movement when quantity is zero', function (): void {
    $this->post(tenantUrl($this->tenant, '/inventory/records'), [
        'name' => 'Empty Shelf',
        'artist' => 'Various',
        'genre' => 'Rock',
        'format' => DiscFormatEnum::LP->value,
        'condition' => DiscConditionEnum::Mint->value,
        'quantity' => 0,
    ])->assertSessionHasNoErrors();

    expect(InventoryMovement::query()->count())->toBe(0);
});

it('validates required fields when storing', function (): void {
    $this->post(tenantUrl($this->tenant, '/inventory/records'), [])
        ->assertSessionHasErrors(['name', 'artist', 'genre', 'format', 'condition', 'quantity']);
});

it('rejects an invalid format enum value', function (): void {
    $this->post(tenantUrl($this->tenant, '/inventory/records'), [
        'name' => 'Bad Format',
        'artist' => 'Someone',
        'genre' => 'Rock',
        'format' => 'cassette-8track',
        'condition' => DiscConditionEnum::Mint->value,
        'quantity' => 1,
    ])->assertSessionHasErrors('format');
});

it('updates a record and logs a correction when quantity changes', function (): void {
    $record = InventoryRecord::factory()->create(['quantity' => 4]);

    $this->put(tenantUrl($this->tenant, "/inventory/records/{$record->id}"), [
        'name' => $record->name,
        'artist' => $record->artist,
        'genre' => $record->genre,
        'format' => $record->format->value,
        'condition' => $record->condition->value,
        'quantity' => 10,
    ])->assertSessionHasNoErrors();

    expect($record->fresh()->quantity)->toBe(10);

    expect(InventoryMovement::query()
        ->where('inventory_record_id', $record->id)
        ->where('type', InventoryMovementTypeEnum::Correction)
        ->exists())->toBeTrue();
});

it('soft deletes a record', function (): void {
    $record = InventoryRecord::factory()->create();

    $this->delete(tenantUrl($this->tenant, "/inventory/records/{$record->id}"))
        ->assertSessionHasNoErrors();

    expect(InventoryRecord::query()->find($record->id))->toBeNull()
        ->and(InventoryRecord::withTrashed()->find($record->id))->not->toBeNull();
});

it('bulk deletes records', function (): void {
    $records = InventoryRecord::factory()->count(3)->create();

    $this->post(tenantUrl($this->tenant, '/inventory/records/bulk-destroy'), [
        'ids' => $records->pluck('id')->all(),
    ])->assertSessionHasNoErrors();

    expect(InventoryRecord::query()->count())->toBe(0);
});

it('shows a single record', function (): void {
    $record = InventoryRecord::factory()->create();

    $this->get(tenantUrl($this->tenant, "/inventory/records/{$record->id}"))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Tenant/Inventory/Show'));
});
