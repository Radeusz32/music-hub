<?php

declare(strict_types=1);

use App\Enums\Tenant\RoleEnum;

it('lists all role values', function (): void {
    expect(RoleEnum::values())->toBe(['owner', 'admin', 'user']);
});

it('builds options with value, label and color for each role', function (): void {
    $options = RoleEnum::options();

    expect($options)->toHaveCount(count(RoleEnum::cases()));

    foreach ($options as $option) {
        expect($option)->toHaveKeys(['value', 'label', 'color'])
            ->and($option['label'])->not->toBe('')
            ->and($option['color'])->toMatch('/^#[0-9a-fA-F]{6}$/');
    }
});

it('gives every role a non-empty label', function (RoleEnum $role): void {
    expect($role->label())->not->toBe('');
})->with(RoleEnum::cases());
