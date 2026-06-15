<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\GuardEnum;
use Spatie\Permission\Models\Permission;

final class InventoryPermissionsSeeder extends PermissionsBaseSeeder
{
    public function run(): void
    {
        $this->seedRecordsPermissions();
        $this->seedMovementsPermissions();
        $this->seedSalesPermissions();
    }

    private function seedRecordsPermissions(): void
    {
        $read = Permission::findOrCreate('inventory-records-read', GuardEnum::Web->value);
        $this->setPermissions($this->all, $read);

        $create = Permission::findOrCreate('inventory-records-create', GuardEnum::Web->value);
        $this->setPermissions($this->all, $create);

        $update = Permission::findOrCreate('inventory-records-update', GuardEnum::Web->value);
        $this->setPermissions($this->all, $update);

        $delete = Permission::findOrCreate('inventory-records-delete', GuardEnum::Web->value);
        $this->setPermissions($this->all, $delete);
    }

    private function seedMovementsPermissions(): void
    {
        $read = Permission::findOrCreate('inventory-movements-read', GuardEnum::Web->value);
        $this->setPermissions($this->all, $read);

        $create = Permission::findOrCreate('inventory-movements-create', GuardEnum::Web->value);
        $this->setPermissions($this->all, $create);

        $delete = Permission::findOrCreate('inventory-movements-delete', GuardEnum::Web->value);
        $this->setPermissions($this->all, $delete);
    }

    private function seedSalesPermissions(): void
    {
        $read = Permission::findOrCreate('inventory-sales-read', GuardEnum::Web->value);
        $this->setPermissions($this->all, $read);

        $create = Permission::findOrCreate('inventory-sales-create', GuardEnum::Web->value);
        $this->setPermissions($this->all, $create);
    }
}
