<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\GuardEnum;
use App\Enums\Tenant\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

abstract class PermissionsBaseSeeder extends Seeder
{
    /** @var array<string, Role> */
    public array $roles;

    /** @var list<string> */
    public array $all = [RoleEnum::Owner->value, RoleEnum::Admin->value, RoleEnum::User->value];

    /** @var list<string> */
    public array $admins = [RoleEnum::Owner->value, RoleEnum::Admin->value];

    /** @var list<string> */
    public array $owners = [RoleEnum::Owner->value];

    public function __construct()
    {
        $this->roles = [
            RoleEnum::Owner->value => Role::findOrCreate(RoleEnum::Owner->value, GuardEnum::Web->value),
            RoleEnum::Admin->value => Role::findOrCreate(RoleEnum::Admin->value, GuardEnum::Web->value),
            RoleEnum::User->value => Role::findOrCreate(RoleEnum::User->value, GuardEnum::Web->value),
        ];
    }

    protected function setPermissions(array $roles, Permission $permission): void
    {
        foreach ($this->roles as $key => $role) {
            if (! in_array($key, $roles)) {
                if ($role->hasPermissionTo($permission->name)) {
                    $role->revokePermissionTo($permission->name);
                }
            } elseif (! $role->hasPermissionTo($permission->name)) {
                $role->givePermissionTo($permission->name);
            }
        }
    }
}
