<?php

declare(strict_types=1);

use App\Enums\Tenant\DiscConditionEnum;

it('builds an option for every condition', function (): void {
    $options = DiscConditionEnum::options();

    expect($options)->toHaveCount(count(DiscConditionEnum::cases()));

    expect(array_column($options, 'value'))
        ->toEqualCanonicalizing(array_column(DiscConditionEnum::cases(), 'value'));
});

it('gives every condition a non-empty label and hex color', function (DiscConditionEnum $condition): void {
    expect($condition->label())->not->toBe('')
        ->and($condition->color())->toMatch('/^#[0-9a-fA-F]{6}$/');
})->with(DiscConditionEnum::cases());
