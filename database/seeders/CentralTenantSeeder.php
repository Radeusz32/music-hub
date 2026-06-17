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
        $tenantSlug = 'beskidvinyl';
        $baseDomain = config('app.base_domain');

        $tenant = Tenant::create([
            'company_name' => 'Beskid Vinyl',
            'tax_id' => '5472158963',
            'regon' => '147852369',
            'krs_number' => '0000456789',
            'company_email' => 'kontakt@beskidvinyl.pl',
            'company_phone' => '338112233',
            'website' => 'https://beskidvinyl.pl',
            'street' => 'Krakowska',
            'building_number' => '12',
            'apartment_number' => '4',
            'postal_code' => '43-300',
            'city' => 'Bielsko-Biała',
            'country' => 'Polska',
        ]);

        $tenant->domains()->create([
            'domain' => "{$tenantSlug}.{$baseDomain}",
        ]);

        $features = Feature::whereIn('name', [
            FeatureEnum::Inventory->value,
            FeatureEnum::Analytics->value,
            FeatureEnum::Users->value,
            FeatureEnum::Settings->value,
        ])->get();

        $tenant->features()->sync($features);
    }
}
