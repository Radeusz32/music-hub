<?php

declare(strict_types=1);

use App\Enums\FeatureEnum;
use App\Models\Central\Domain;
use App\Models\Central\Feature;

it('casts is_active to a boolean', function (): void {
    $tenant = createTenant(active: true);

    expect($tenant->refresh()->is_active)->toBeTrue();
});

it('persists custom columns as real columns', function (): void {
    $tenant = createTenant();

    expect($tenant->refresh()->company_name)->toBe('Test Company');
});

it('lists its custom columns', function (): void {
    expect(App\Models\Central\Tenant::getCustomColumns())
        ->toContain('id', 'company_name', 'is_active');
});

it('relates to its domains', function (): void {
    $tenant = createTenant(domain: 'relations.test');

    expect($tenant->domains->pluck('domain'))->toContain('relations.test')
        ->and($tenant->domains->first())->toBeInstanceOf(Domain::class);
});

it('relates to its features', function (): void {
    $tenant = createTenant(features: [FeatureEnum::Inventory, FeatureEnum::Users]);

    expect($tenant->features)->toHaveCount(2)
        ->and($tenant->features->first())->toBeInstanceOf(Feature::class);
});
