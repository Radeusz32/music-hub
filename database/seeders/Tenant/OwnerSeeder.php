<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

final class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->owner()->create();
    }
}
