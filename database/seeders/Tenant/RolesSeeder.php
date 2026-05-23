<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\GuardEnum;
use App\Enums\Tenant\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

final class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value, GuardEnum::Web->value);
        }
    }
}
