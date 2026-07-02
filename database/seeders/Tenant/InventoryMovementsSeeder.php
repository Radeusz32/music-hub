<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Models\Tenant\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class InventoryMovementsSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->first();

        /** @var Collection<int, InventoryRecord> $records */
        $records = InventoryRecord::query()->get(['id', 'created_at', 'quantity', 'purchase_price_per_unit']);

        $this->seedInitialMovements($records, $owner);
        $this->seedSales($records, $owner);
    }

    /**
     * @param  Collection<int, InventoryRecord>  $records
     */
    private function seedInitialMovements(Collection $records, ?User $owner): void
    {
        $records->each(function (InventoryRecord $record) use ($owner): void {
            $date = $record->created_at ?? Carbon::now();

            $movement = InventoryMovement::factory()->make([
                'inventory_record_id' => $record->id,
                'type' => InventoryMovementTypeEnum::Initial,
                'quantity' => $record->quantity,
                'quantity_before' => 0,
                'quantity_after' => $record->quantity,
                'sale_price' => null,
                'note' => 'Stan początkowy przy utworzeniu pozycji.',
                'user_id' => $owner?->id,
            ]);

            $this->persistAt($movement, $date);
        });
    }

    /**
     * Generates Sale movements spread across the last 12 months. Monthly volume
     * grows toward the present (a realistic upward trend) and the last 30 days
     * get extra daily density so the analytics revenue charts read well.
     *
     * @param  Collection<int, InventoryRecord>  $records
     */
    private function seedSales(Collection $records, ?User $owner): void
    {
        if ($records->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
            $monthStart = $now->copy()->startOfMonth()->subMonths($monthsAgo);
            $daysInMonth = $monthStart->daysInMonth;
            $salesThisMonth = (int) round(14 + (11 - $monthsAgo) * 2.5);

            for ($i = 0; $i < $salesThisMonth; $i++) {
                $date = $monthStart->copy()
                    ->addDays(fake()->numberBetween(0, $daysInMonth - 1))
                    ->setTime(fake()->numberBetween(9, 20), fake()->numberBetween(0, 59));

                if ($date->isFuture()) {
                    $date = $now->copy()->subHours(fake()->numberBetween(1, 48));
                }

                $this->makeSale($records->random(), $owner, $date);
            }
        }

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $count = fake()->numberBetween(1, 5);

            for ($i = 0; $i < $count; $i++) {
                $date = $now->copy()
                    ->subDays($daysAgo)
                    ->setTime(fake()->numberBetween(9, 20), fake()->numberBetween(0, 59));

                $this->makeSale($records->random(), $owner, $date);
            }
        }
    }

    private function makeSale(InventoryRecord $record, ?User $owner, CarbonInterface $date): void
    {
        $basePrice = (float) ($record->purchase_price_per_unit ?? fake()->randomFloat(2, 30, 250));
        $salePrice = round($basePrice * fake()->randomFloat(2, 1.25, 2.1), 2);

        $movement = InventoryMovement::factory()->sale()->make([
            'inventory_record_id' => $record->id,
            'sale_price' => $salePrice,
            'user_id' => $owner?->id,
        ]);

        $this->persistAt($movement, $date);
    }

    private function persistAt(InventoryMovement $movement, CarbonInterface $date): void
    {
        $movement->created_at = $date;
        $movement->updated_at = $date;
        $movement->save();
    }
}
