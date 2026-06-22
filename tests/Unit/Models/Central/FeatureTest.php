<?php

declare(strict_types=1);

use App\Enums\FeatureEnum;
use App\Models\Central\Feature;

it('casts the name to a FeatureEnum', function (): void {
    $feature = Feature::firstOrCreate(
        ['name' => FeatureEnum::Inventory->value],
        ['label' => FeatureEnum::Inventory->label()],
    );

    expect($feature->refresh()->name)->toBe(FeatureEnum::Inventory);
});

it('belongs to many tenants', function (): void {
    $tenant = createTenant(features: [FeatureEnum::Inventory]);

    $feature = Feature::query()->where('name', FeatureEnum::Inventory->value)->first();

    expect($feature->tenants->pluck('id'))->toContain($tenant->id);
});
