<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

final class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'first_name' => 'Anna',
            'last_name' => 'Kowalska',
            'email' => 'admin@music1.test',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::factory()->count(2)->admin()->create();
        User::factory()->count(9)->regularUser()->create();

        // A few edge-case rows so the status / verification filters have data.
        User::factory()->count(2)->regularUser()->inactive()->create();
        User::factory()->count(2)->regularUser()->unverified()->create();
    }
}
