<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\GuardEnum;
use Spatie\Permission\Models\Permission;

final class UsersPermissionsSeeder extends PermissionsBaseSeeder
{
    public function run(): void
    {
        $read = Permission::findOrCreate('users-read', GuardEnum::Web->value);
        $this->setPermissions($this->admins, $read);

        $create = Permission::findOrCreate('users-create', GuardEnum::Web->value);
        $this->setPermissions($this->admins, $create);

        $update = Permission::findOrCreate('users-update', GuardEnum::Web->value);
        $this->setPermissions($this->admins, $update);

        $delete = Permission::findOrCreate('users-delete', GuardEnum::Web->value);
        $this->setPermissions($this->admins, $delete);
    }
}
