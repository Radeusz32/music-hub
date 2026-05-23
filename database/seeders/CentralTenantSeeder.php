<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FeatureEnum;
use App\Models\Central\Feature;
use App\Models\Central\Tenant;
use Illuminate\Database\Seeder;

final class CentralTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenantSlug = 'music1';
        $baseDomain = config('app.base_domain');

        $tenant = Tenant::create();

        $tenant->domains()->create([
            'domain' => "{$tenantSlug}.{$baseDomain}",
        ]);

        $features = Feature::whereIn('name', [
            FeatureEnum::Inventory->value,
        ])->get();

        $tenant->features()->sync($features);
    }
}
