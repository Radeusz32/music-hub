<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Central\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class CentralAdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'superadmin@musichub.pl'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
