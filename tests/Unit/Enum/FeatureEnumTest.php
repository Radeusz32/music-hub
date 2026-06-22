<?php

declare(strict_types=1);

use App\Enums\FeatureEnum;

it('covers every application feature', function (): void {
    expect(array_column(FeatureEnum::cases(), 'value'))
        ->toEqualCanonicalizing([
            'inventory',
            'trading',
            'analytics',
            'integrations',
            'users',
            'settings',
        ]);
});

it('gives every feature a non-empty label', function (FeatureEnum $feature): void {
    expect($feature->label())->not->toBe('');
})->with(FeatureEnum::cases());
