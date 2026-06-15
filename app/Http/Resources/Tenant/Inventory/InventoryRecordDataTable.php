<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\Inventory;

use App\Enums\FilterTypeEnum;
use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use App\Http\Resources\DataTableConfig;
use App\Models\Tenant\InventoryRecord;
use App\Transformers\Tenant\Inventory\InventoryRecordTransformer;
use App\Transformers\Transformer;
use Illuminate\Database\Eloquent\Builder;

final class InventoryRecordDataTable extends DataTableConfig
{
    /* ── DataTable query configuration ── */

    /** @return Builder<InventoryRecord> */
    public static function baseQuery(): Builder
    {
        return InventoryRecord::query()->with(self::transformer()::eagerLoads());
    }

    /** @return list<string> */
    public static function searchableColumns(): array
    {
        return ['name', 'artist', 'genre', 'label', 'catalog_number', 'barcode'];
    }

    /** @return list<string> */
    public static function allowedSortColumns(): array
    {
        return ['name', 'artist', 'genre', 'format', 'condition', 'year', 'quantity', 'created_at', 'updated_at'];
    }

    /**
     * @return array<string, array{column: string, type: FilterTypeEnum, options?: array<int, array{value: string, label: string}>}>
     */
    public static function filterableColumns(): array
    {
        return [
            'format' => [
                'column' => 'format',
                'type' => FilterTypeEnum::Select,
                'options' => DiscFormatEnum::options(),
            ],
            'condition' => [
                'column' => 'condition',
                'type' => FilterTypeEnum::Select,
                'options' => DiscConditionEnum::options(),
            ],
            'genre' => [
                'column' => 'genre',
                'type' => FilterTypeEnum::Select,
            ],
            'year' => [
                'column' => 'year',
                'type' => FilterTypeEnum::NumberRange,
            ],
            'quantity' => [
                'column' => 'quantity',
                'type' => FilterTypeEnum::NumberRange,
            ],
            'created_at' => [
                'column' => 'created_at',
                'type' => FilterTypeEnum::DateRange,
            ],
            'updated_at' => [
                'column' => 'updated_at',
                'type' => FilterTypeEnum::DateRange,
            ],
        ];
    }

    public static function defaultSort(): string
    {
        return 'name';
    }

    public static function defaultDirection(): string
    {
        return 'asc';
    }

    public static function defaultPerPage(): int
    {
        return 20;
    }

    protected static function transformer(): Transformer
    {
        return new InventoryRecordTransformer();
    }
}
