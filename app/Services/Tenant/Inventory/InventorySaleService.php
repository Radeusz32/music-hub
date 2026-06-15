<?php

declare(strict_types=1);

namespace App\Services\Tenant\Inventory;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Transformers\Tenant\Inventory\InventoryMovementTransformer;
use Illuminate\Support\Carbon;

final class InventorySaleService
{
    public function __construct(private readonly InventoryRecordMovementsService $movements) {}

    /**
     * Records currently in stock, shaped for the point-of-sale grid.
     *
     * @return list<array{id: int, name: string, artist: string, format: string, condition: string, quantity: int, cover_image: string|null}>
     */
    public function sellableRecords(): array
    {
        return InventoryRecord::query()
            ->where('quantity', '>', 0)
            ->with('media')
            ->orderBy('artist')
            ->orderBy('name')
            ->get()
            ->map(fn (InventoryRecord $record): array => [
                'id' => $record->id,
                'name' => $record->name,
                'artist' => $record->artist,
                'format' => $record->format->value,
                'condition' => $record->condition->value,
                'quantity' => $record->quantity,
                'cover_image' => $record->getFirstMediaUrl('cover') ?: null,
            ])
            ->all();
    }

    /**
     * The most recent sales, newest first, for the side feed.
     *
     * @return list<array<string, mixed>>
     */
    public function recentSales(int $limit = 8): array
    {
        $transformer = new InventoryMovementTransformer();

        return InventoryMovement::query()
            ->where('type', InventoryMovementTypeEnum::Sale)
            ->with(InventoryMovementTransformer::eagerLoads())
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (InventoryMovement $movement): array => $transformer->toArray($movement, request()))
            ->all();
    }

    /**
     * Aggregate counters for sales recorded today.
     *
     * @return array{transactions: int, units: int}
     */
    public function todayStats(): array
    {
        $today = InventoryMovement::query()
            ->where('type', InventoryMovementTypeEnum::Sale)
            ->whereDate('created_at', Carbon::today());

        return [
            'transactions' => (clone $today)->count(),
            'units' => (int) (clone $today)->sum('quantity'),
        ];
    }

    /**
     * Sell a record: delegate the stock mutation and ledger write to the
     * movements service (single source of truth for stock changes).
     */
    public function sell(
        InventoryRecord $record,
        int $quantity = 1,
        ?string $salePrice = null,
        ?string $note = null,
        ?int $userId = null,
    ): InventoryMovement {
        return $this->movements->recordSale($record, $quantity, $salePrice, $note, $userId);
    }
}
