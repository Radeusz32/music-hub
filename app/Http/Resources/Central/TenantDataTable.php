<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Enums\FilterTypeEnum;
use App\Http\Resources\DataTableConfig;
use App\Models\Central\Tenant;
use App\Transformers\Central\TenantTransformer;
use App\Transformers\Transformer;
use Illuminate\Database\Eloquent\Builder;

final class TenantDataTable extends DataTableConfig
{
    /* ── DataTable query configuration ── */

    /** @return Builder<Tenant> */
    public static function baseQuery(): Builder
    {
        return Tenant::query()->with(self::transformer()::eagerLoads());
    }

    /** @return list<string> */
    public static function searchableColumns(): array
    {
        return ['company_name', 'tax_id', 'regon', 'krs_number', 'company_email', 'city'];
    }

    /** @return list<string> */
    public static function allowedSortColumns(): array
    {
        return ['company_name', 'city', 'country', 'created_at', 'updated_at'];
    }

    /**
     * @return array<string, array{column: string, type: FilterTypeEnum, options?: array<int, array{value: string, label: string}>}>
     */
    public static function filterableColumns(): array
    {
        return [
            'is_active' => [
                'column' => 'is_active',
                'type' => FilterTypeEnum::Boolean,
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
        return 'company_name';
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
        return new TenantTransformer();
    }
}
