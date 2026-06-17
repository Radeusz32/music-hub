<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\GuardEnum;
use Spatie\Permission\Models\Permission;

final class AnalyticsPermissionsSeeder extends PermissionsBaseSeeder
{
    public function run(): void
    {
        $read = Permission::findOrCreate('analytics-read', GuardEnum::Web->value);
        $this->setPermissions($this->admins, $read);
    }
}
