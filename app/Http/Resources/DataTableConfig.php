<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\FilterTypeEnum;
use App\Transformers\Transformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class DataTableConfig extends JsonResource
{
    abstract protected static function transformer(): Transformer;

    /** @return Builder<\Illuminate\Database\Eloquent\Model> */
    abstract public static function baseQuery(): Builder;

    /** @return list<string> */
    abstract public static function searchableColumns(): array;

    /** @return list<string> */
    abstract public static function allowedSortColumns(): array;

    /**
     * Filterable columns configuration keyed by request param.
     * Format: ['key' => ['column' => 'db_column', 'type' => FilterTypeEnum, 'options' => [...]]]
     *
     * @return array<string, array{column: string, type: FilterTypeEnum, options?: array<int, array{value: string, label: string}>}>
     */
    abstract public static function filterableColumns(): array;

    abstract public static function defaultSort(): string;

    abstract public static function defaultDirection(): string;

    abstract public static function defaultPerPage(): int;

    /**
     * @return array<string, mixed>
     */
    final public function toArray(Request $request): array
    {
        return static::transformer()->toArray($this->resource, $request);
    }
}
