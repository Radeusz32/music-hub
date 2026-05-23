<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Tenant\AllPermissionsSeeder;
use Database\Seeders\Tenant\OwnerSeeder;
use Database\Seeders\Tenant\RolesSeeder;
use Illuminate\Database\Seeder;

final class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AllPermissionsSeeder::class,
            OwnerSeeder::class,
        ]);
    }
}
