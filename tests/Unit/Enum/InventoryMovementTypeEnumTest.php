<?php

declare(strict_types=1);

use App\Enums\Tenant\InventoryMovementTypeEnum;

it('returns the correct stock sign for each type', function (InventoryMovementTypeEnum $type, int $expected): void {
    expect($type->sign())->toBe($expected);
})->with([
    'initial increases' => [InventoryMovementTypeEnum::Initial, 1],
    'in increases' => [InventoryMovementTypeEnum::In, 1],
    'return increases' => [InventoryMovementTypeEnum::Return, 1],
    'correction increases' => [InventoryMovementTypeEnum::Correction, 1],
    'out decreases' => [InventoryMovementTypeEnum::Out, -1],
    'sale decreases' => [InventoryMovementTypeEnum::Sale, -1],
    'loss decreases' => [InventoryMovementTypeEnum::Loss, -1],
]);

it('exposes every type as an option', function (): void {
    $options = InventoryMovementTypeEnum::options();

    expect($options)->toHaveCount(count(InventoryMovementTypeEnum::cases()));

    foreach ($options as $option) {
        expect($option)->toHaveKeys(['value', 'label', 'color'])
            ->and($option['label'])->not->toBe('')
            ->and($option['color'])->toStartWith('#');
    }
});

it('only offers manual movement types in manualOptions', function (): void {
    $values = array_column(InventoryMovementTypeEnum::manualOptions(), 'value');

    expect($values)->toEqualCanonicalizing([
        InventoryMovementTypeEnum::In->value,
        InventoryMovementTypeEnum::Out->value,
        InventoryMovementTypeEnum::Return->value,
        InventoryMovementTypeEnum::Loss->value,
    ])
        ->and($values)->not->toContain(InventoryMovementTypeEnum::Initial->value)
        ->and($values)->not->toContain(InventoryMovementTypeEnum::Correction->value)
        ->and($values)->not->toContain(InventoryMovementTypeEnum::Sale->value);
});

it('gives every case a non-empty label and hex color', function (InventoryMovementTypeEnum $type): void {
    expect($type->label())->not->toBe('')
        ->and($type->color())->toMatch('/^#[0-9a-fA-F]{6}$/');
})->with(InventoryMovementTypeEnum::cases());
