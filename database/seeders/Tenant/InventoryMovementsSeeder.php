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

        $records->each(fn (InventoryRecord $record) => $this->seedLedgerFor($record, $owner));
    }

    /**
     * Builds a coherent movement history for a single record: one intake that
     * establishes the starting stock, followed by single-unit sales spread over
     * time that chain down to the record's current quantity. Sale dates are
     * weighted toward the present so the analytics charts show a realistic
     * upward trend with denser recent activity.
     */
    private function seedLedgerFor(InventoryRecord $record, ?User $owner): void
    {
        $createdAt = $record->created_at ?? Carbon::now();
        $now = Carbon::now();

        $currentStock = (int) $record->quantity;
        $salesCount = $this->planSalesCount($currentStock, $createdAt, $now);
        $initialStock = $currentStock + $salesCount;

        $this->seedInitialMovement($record, $owner, $createdAt, $initialStock);

        $running = $initialStock;

        foreach ($this->saleDates($salesCount, $createdAt, $now) as $date) {
            $this->seedSale($record, $owner, $date, $running);
            $running--;
        }
    }

    private function seedInitialMovement(InventoryRecord $record, ?User $owner, CarbonInterface $date, int $initialStock): void
    {
        $movement = InventoryMovement::factory()->make([
            'inventory_record_id' => $record->id,
            'type' => InventoryMovementTypeEnum::Initial,
            'quantity' => $initialStock,
            'quantity_before' => 0,
            'quantity_after' => $initialStock,
            'sale_price' => null,
            'note' => 'Stan początkowy przy utworzeniu pozycji.',
            'user_id' => $owner?->id,
        ]);

        $this->persistAt($movement, $date);
    }

    private function seedSale(InventoryRecord $record, ?User $owner, CarbonInterface $date, int $stockBefore): void
    {
        $basePrice = (float) ($record->purchase_price_per_unit ?? fake()->randomFloat(2, 30, 250));
        $salePrice = round($basePrice * fake()->randomFloat(2, 1.25, 2.1), 2);

        $movement = InventoryMovement::factory()->make([
            'inventory_record_id' => $record->id,
            'type' => InventoryMovementTypeEnum::Sale,
            'quantity' => 1,
            'quantity_before' => $stockBefore,
            'quantity_after' => $stockBefore - 1,
            'sale_price' => $salePrice,
            'note' => null,
            'user_id' => $owner?->id,
        ]);

        $this->persistAt($movement, $date);
    }

    /**
     * Number of single-unit sales a record has seen, scaled by how long it has
     * been in stock. Records that are currently sold out always sold at least
     * one copy.
     */
    private function planSalesCount(int $currentStock, CarbonInterface $createdAt, CarbonInterface $now): int
    {
        $ageDays = max(1, (int) $createdAt->diffInDays($now));
        $count = intdiv($ageDays, fake()->numberBetween(35, 60)) + fake()->numberBetween(0, 2);

        $count = min($count, 9);

        if ($currentStock === 0) {
            $count = max($count, 1);
        }

        return $count;
    }

    /**
     * Sale timestamps between the intake date and now, weighted toward the
     * present, returned in chronological order.
     *
     * @return list<CarbonInterface>
     */
    private function saleDates(int $salesCount, CarbonInterface $createdAt, CarbonInterface $now): array
    {
        if ($salesCount === 0) {
            return [];
        }

        $span = max(1, $createdAt->diffInSeconds($now));

        $dates = [];

        for ($i = 0; $i < $salesCount; $i++) {
            $weight = fake()->randomFloat(4, 0, 1) ** 0.6;

            $date = $createdAt->copy()
                ->addSeconds((int) round($span * $weight))
                ->setTime(fake()->numberBetween(9, 20), fake()->numberBetween(0, 59));

            if ($date->lessThanOrEqualTo($createdAt)) {
                $date = $createdAt->copy()->addHour();
            }

            if ($date->isFuture()) {
                $date = $now->copy()->subHours(fake()->numberBetween(1, 48));
            }

            $dates[] = $date;
        }

        usort($dates, fn (CarbonInterface $a, CarbonInterface $b): int => $a <=> $b);

        return $dates;
    }

    private function persistAt(InventoryMovement $movement, CarbonInterface $date): void
    {
        $movement->created_at = $date;
        $movement->updated_at = $date;
        $movement->save();
    }
}
