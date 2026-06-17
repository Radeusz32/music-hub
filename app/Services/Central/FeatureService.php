<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\FeatureEnum;
use App\Models\Central\Feature;
use App\Models\Central\Tenant;

final class FeatureService
{
    /**
     * All available modules, shaped for the frontend.
     *
     * @return list<array{value: string, label: string}>
     */
    public function availableFeatures(): array
    {
        return array_map(
            fn (FeatureEnum $feature): array => [
                'value' => $feature->value,
                'label' => $feature->label(),
            ],
            FeatureEnum::cases(),
        );
    }

    /**
     * All tenants with their currently enabled module names + active state.
     *
     * @return list<array{id: string, company_name: string|null, is_active: bool, features: list<string>}>
     */
    public function tenantsWithFeatures(): array
    {
        return Tenant::query()
            ->with('features')
            ->orderBy('company_name')
            ->get()
            ->map(fn (Tenant $tenant): array => [
                'id' => $tenant->id,
                'company_name' => $tenant->company_name,
                'is_active' => (bool) $tenant->is_active,
                'features' => $tenant->features
                    ->pluck('name')
                    ->map(fn (FeatureEnum $name): string => $name->value)
                    ->values()
                    ->all(),
            ])
            ->all();
    }

    /**
     * Enable the module for the tenant if missing, disable it otherwise.
     */
    public function toggle(Tenant $tenant, FeatureEnum $feature): void
    {
        $model = Feature::query()
            ->where('name', $feature->value)
            ->firstOrFail();

        $tenant->features()->toggle($model->id);
    }
}
