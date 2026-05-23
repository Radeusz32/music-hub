<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FeatureEnum;
use App\Models\Central\Feature;
use Illuminate\Database\Seeder;

final class FeaturesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FeatureEnum::cases() as $feature) {
            Feature::updateOrCreate(
                ['name' => $feature->value],
                ['label' => $feature->label()],
            );
        }
    }
}
