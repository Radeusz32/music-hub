<?php

declare(strict_types=1);

use App\Enums\Tenant\DiscFormatEnum;

it('builds an option for every format', function (): void {
    $options = DiscFormatEnum::options();

    expect($options)->toHaveCount(count(DiscFormatEnum::cases()));

    expect(array_column($options, 'value'))
        ->toEqualCanonicalizing(array_column(DiscFormatEnum::cases(), 'value'));
});

it('gives every format a non-empty label and hex color', function (DiscFormatEnum $format): void {
    expect($format->label())->not->toBe('')
        ->and($format->color())->toMatch('/^#[0-9a-fA-F]{6}$/');
})->with(DiscFormatEnum::cases());

it('shares a color between vinyl LP variants', function (): void {
    expect(DiscFormatEnum::LP->color())->toBe(DiscFormatEnum::DoubleLP->color());
});
