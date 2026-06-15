<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

final class InventoryMovementsSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->first();

        InventoryRecord::query()
            ->get(['id', 'quantity'])
            ->each(function (InventoryRecord $record) use ($owner): void {
                InventoryMovement::factory()->create([
                    'inventory_record_id' => $record->id,
                    'type' => InventoryMovementTypeEnum::Initial,
                    'quantity' => $record->quantity,
                    'quantity_before' => 0,
                    'quantity_after' => $record->quantity,
                    'note' => 'Stan początkowy przy utworzeniu pozycji.',
                    'user_id' => $owner?->id,
                ]);
            });
    }
}
