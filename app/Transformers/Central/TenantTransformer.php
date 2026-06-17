<?php

declare(strict_types=1);

namespace App\Transformers\Central;

use App\Models\Central\Domain;
use App\Models\Central\Feature;
use App\Models\Central\Tenant;
use App\Transformers\Transformer;

final class TenantTransformer extends Transformer
{
    protected array $defaultIncludes = [
        'domains',
        'features',
    ];

    protected array $availableIncludes = [
        //
    ];

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return ['domains', 'features'];
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(mixed $model): array
    {
        /** @var Tenant $model */
        return [
            'id' => $model->id,
            'company_name' => $model->company_name,
            'is_active' => (bool) $model->is_active,
            'tax_id' => $model->tax_id,
            'regon' => $model->regon,
            'krs_number' => $model->krs_number,
            'company_email' => $model->company_email,
            'company_phone' => $model->company_phone,
            'website' => $model->website,
            'street' => $model->street,
            'building_number' => $model->building_number,
            'apartment_number' => $model->apartment_number,
            'postal_code' => $model->postal_code,
            'city' => $model->city,
            'country' => $model->country,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }

    /**
     * @return list<string>
     */
    public function includeDomains(mixed $model, mixed $_request = null): array
    {
        /** @var Tenant $model */
        if (! $model->relationLoaded('domains')) {
            return [];
        }

        return $model->domains
            ->map(fn (Domain $domain): string => $domain->domain)
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function includeFeatures(mixed $model, mixed $_request = null): array
    {
        /** @var Tenant $model */
        if (! $model->relationLoaded('features')) {
            return [];
        }

        return $model->features
            ->map(fn (Feature $feature): array => [
                'value' => $feature->name->value,
                'label' => $feature->label,
            ])
            ->all();
    }
}
