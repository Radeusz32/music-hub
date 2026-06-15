<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\Inventory;

use App\Enums\FilterTypeEnum;
use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Http\Resources\DataTableConfig;
use App\Models\Tenant\InventoryMovement;
use App\Transformers\Tenant\Inventory\InventoryMovementTransformer;
use App\Transformers\Transformer;
use Illuminate\Database\Eloquent\Builder;

final class InventoryMovementDataTable extends DataTableConfig
{
    /* ── DataTable query configuration ── */

    /** @return Builder<InventoryMovement> */
    public static function baseQuery(): Builder
    {
        return InventoryMovement::query()->with(self::transformer()::eagerLoads());
    }

    /** @return list<string> */
    public static function searchableColumns(): array
    {
        return ['note'];
    }

    /** @return list<string> */
    public static function allowedSortColumns(): array
    {
        return ['type', 'quantity', 'quantity_after', 'created_at'];
    }

    /**
     * @return array<string, array{column: string, type: FilterTypeEnum, relation?: string, options?: array<int, array{value: string, label: string}>}>
     */
    public static function filterableColumns(): array
    {
        return [
            'type' => [
                'column' => 'type',
                'type' => FilterTypeEnum::Select,
                'options' => InventoryMovementTypeEnum::options(),
            ],
            'inventory_record_id' => [
                'column' => 'inventory_record_id',
                'type' => FilterTypeEnum::Number,
            ],
            'created_at' => [
                'column' => 'created_at',
                'type' => FilterTypeEnum::DateRange,
            ],
        ];
    }

    public static function defaultSort(): string
    {
        return 'created_at';
    }

    public static function defaultDirection(): string
    {
        return 'desc';
    }

    public static function defaultPerPage(): int
    {
        return 20;
    }

    protected static function transformer(): Transformer
    {
        return new InventoryMovementTransformer();
    }
}
