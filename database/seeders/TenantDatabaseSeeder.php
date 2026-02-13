<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant\User;
use Spatie\Permission\Models\Role;

final class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();
        $this->seedOwner();
    }

    private function seedRoles(): void
    {
        Role::create([
            'name' => 'owner',
            'guard_name' => 'tenant',
        ]);
    }

    private function seedOwner(): void
    {
        $user = User::create([
            'name' => 'Tenant Owner',
            'email' => 'owner@music1.test',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole('owner');
    }
}
