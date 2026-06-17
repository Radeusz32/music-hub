<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\GuardEnum;
use Spatie\Permission\Models\Permission;

final class SettingsPermissionsSeeder extends PermissionsBaseSeeder
{
    public function run(): void
    {
        $profile = Permission::findOrCreate('setting-profile', GuardEnum::Web->value);
        $this->setPermissions($this->all, $profile);

        $organization = Permission::findOrCreate('setting-organization', GuardEnum::Web->value);
        $this->setPermissions($this->admins, $organization);
    }
}
