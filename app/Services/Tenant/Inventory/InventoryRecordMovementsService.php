<?php

declare(strict_types=1);

namespace App\Services\Tenant\Inventory;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Http\Resources\Tenant\Inventory\InventoryMovementDataTable;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class InventoryRecordMovementsService extends BaseService
{
    /**
     * @return array<string, mixed>
     */
    public function index(Request $request): array
    {
        return $this->fetchForDataTable(InventoryMovementDataTable::class, $request);
    }

    /**
     * Options for the record picker in the movement form.
     *
     * @return list<array{value: int, label: string, quantity: int}>
     */
    public function recordOptions(): array
    {
        return InventoryRecord::query()
            ->orderBy('artist')
            ->orderBy('name')
            ->get(['id', 'name', 'artist', 'quantity'])
            ->map(fn (InventoryRecord $record): array => [
                'value' => $record->id,
                'label' => "{$record->artist} – {$record->name}",
                'quantity' => $record->quantity,
            ])
            ->all();
    }

    /**
     * Record a manual warehouse movement: apply its delta to the record's
     * stock and persist the ledger entry. Throws when an outbound movement
     * would drive the stock below zero.
     */
    public function record(
        InventoryRecord $record,
        InventoryMovementTypeEnum $type,
        int $quantity,
        ?string $note = null,
        ?int $userId = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($record, $type, $quantity, $note, $userId): InventoryMovement {
            $record->refresh();

            $before = $record->quantity;
            $after = $before + ($type->sign() * $quantity);

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Nie można wydać {$quantity} szt. - dostępny stan magazynowy to {$before} szt.",
                ]);
            }

            $record->update(['quantity' => $after]);

            return $this->writeEntry($record, $type, $quantity, $before, $after, $note, $userId);
        });
    }

    /**
     * Record a sale: decrease the record's stock and persist a `Sale` ledger
     * entry. Throws when the sale would drive the stock below zero.
     */
    public function recordSale(
        InventoryRecord $record,
        int $quantity = 1,
        ?string $salePrice = null,
        ?string $note = null,
        ?int $userId = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($record, $quantity, $salePrice, $note, $userId): InventoryMovement {
            $record->refresh();

            $before = $record->quantity;
            $after = $before - $quantity;

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Nie można sprzedać {$quantity} szt. - dostępny stan magazynowy to {$before} szt.",
                ]);
            }

            $record->update(['quantity' => $after]);

            return $this->writeEntry(
                $record,
                InventoryMovementTypeEnum::Sale,
                $quantity,
                $before,
                $after,
                $note,
                $userId,
                $salePrice,
            );
        });
    }

    /**
     * Log the opening stock for a freshly created record without mutating it
     * (the record already carries the quantity).
     */
    public function recordInitial(InventoryRecord $record, ?int $userId = null): ?InventoryMovement
    {
        if ($record->quantity <= 0) {
            return null;
        }

        return $this->writeEntry(
            $record,
            InventoryMovementTypeEnum::Initial,
            $record->quantity,
            0,
            $record->quantity,
            'Stan początkowy przy utworzeniu pozycji.',
            $userId,
        );
    }

    /**
     * Log a stock correction made while editing a record. The record's
     * quantity is already updated, so this only writes the ledger entry.
     */
    public function recordQuantityChange(
        InventoryRecord $record,
        int $oldQuantity,
        int $newQuantity,
        ?int $userId = null,
    ): ?InventoryMovement {
        if ($oldQuantity === $newQuantity) {
            return null;
        }

        return $this->writeEntry(
            $record,
            InventoryMovementTypeEnum::Correction,
            abs($newQuantity - $oldQuantity),
            $oldQuantity,
            $newQuantity,
            'Korekta stanu przy edycji pozycji.',
            $userId,
        );
    }

    /**
     * Delete a movement and reverse its effect on the record's stock.
     */
    public function delete(InventoryMovement $movement): void
    {
        DB::transaction(function () use ($movement): void {
            $this->reverseStock($movement);
            $movement->delete();
        });
    }

    /**
     * Delete multiple movements, reversing each one's effect on stock.
     * Returns the number of movements deleted.
     *
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $movements = InventoryMovement::query()->whereIn('id', $ids)->get();

            foreach ($movements as $movement) {
                $this->reverseStock($movement);
                $movement->delete();
            }

            return $movements->count();
        });
    }

    private function writeEntry(
        InventoryRecord $record,
        InventoryMovementTypeEnum $type,
        int $quantity,
        int $before,
        int $after,
        ?string $note,
        ?int $userId,
        ?string $salePrice = null,
    ): InventoryMovement {
        return InventoryMovement::query()->create([
            'inventory_record_id' => $record->id,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'sale_price' => $salePrice,
            'note' => $note,
            'user_id' => $userId,
        ]);
    }

    private function reverseStock(InventoryMovement $movement): void
    {
        $record = $movement->inventoryRecord()->first();

        if ($record === null) {
            return;
        }

        $delta = $movement->quantity_after - $movement->quantity_before;

        $record->update(['quantity' => max(0, $record->quantity - $delta)]);
    }
}
