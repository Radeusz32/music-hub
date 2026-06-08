<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

final class AllPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InventoryPermissionsSeeder::class,
            UsersPermissionsSeeder::class,
        ]);
    }
}
