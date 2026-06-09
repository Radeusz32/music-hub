<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FilterTypeEnum;
use App\Http\Resources\DataTableConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseService
{
    /**
     * Runs a paginated DataTable query driven by a DataTableConfig class.
     * All params (search, filters, sort, perPage) are read from the Request
     * using the resource's own configuration as defaults.
     * Returns the paginator array merged with an active `filters` key.
     *
     * @param  class-string<DataTableConfig>  $resourceClass
     * @return array<string, mixed>
     */
    final public function fetchForDataTable(
        string $resourceClass,
        Request $request,
    ): array {
        $query = $resourceClass::baseQuery();

        // Apply search
        $search = $request->query('search');
        if ($search !== null && $search !== '') {
            $this->applySearch($query, $search, $resourceClass::searchableColumns());
        }

        // Resolve and apply all filters
        $filterResults = $this->resolveFilters($resourceClass, $request);
        $this->applyFilters($query, $filterResults['resolved']);

        // Apply sorting
        $resolvedSort = $this->resolveSortColumn($resourceClass, $request->query('sortBy'));
        $direction = $request->query('direction', $resourceClass::defaultDirection());
        $resolvedDirection = in_array($direction, ['asc', 'desc'], true) ? $direction : $resourceClass::defaultDirection();
        $this->applySorting($query, $resolvedSort, $resolvedDirection);

        // Apply pagination
        $perPage = (int) $request->query('perPage', $resourceClass::defaultPerPage());
        $resolvedPerPage = $perPage > 0 ? $perPage : $resourceClass::defaultPerPage();

        $paginator = $query
            ->paginate($resolvedPerPage)
            ->withQueryString()
            ->through(fn ($record) => (new $resourceClass($record))->toArray(request()));

        // Merge active filters
        $filters = $request->only(array_merge(
            ['search', 'sortBy', 'direction', 'perPage'],
            $filterResults['keys'],
        ));

        return array_merge($paginator->toArray(), ['filters' => $filters]);
    }

    /**
     * Resolve filters from the request based on the resource's filterableColumns() config.
     * Returns resolved filters and all filter keys for query string preservation.
     *
     * @param  class-string<DataTableConfig>  $resourceClass
     * @return array{resolved: array<int, array{type: FilterTypeEnum, column: string, value?: mixed, from?: string|null, to?: string|null, relation?: string|null}>, keys: list<string>}
     */
    protected function resolveFilters(string $resourceClass, Request $request): array
    {
        $resolved = [];
        $allKeys = [];

        foreach ($resourceClass::filterableColumns() as $key => $config) {
            $type = $config['type'];
            $column = $config['column'];

            if ($type->isRange()) {
                [$fromKey, $toKey] = ["{$key}_from", "{$key}_to"];
                $from = $request->query($fromKey);
                $to = $request->query($toKey);

                if (($from !== null && $from !== '') || ($to !== null && $to !== '')) {
                    $resolved[] = [
                        'type' => $type,
                        'column' => $column,
                        'from' => $from,
                        'to' => $to,
                    ];
                }

                $allKeys[] = $fromKey;
                $allKeys[] = $toKey;

                continue;
            }

            $value = $request->query($key);

            if ($value !== null && $value !== '') {
                $resolved[] = [
                    'type' => $type,
                    'column' => $column,
                    'value' => $value,
                    'relation' => $config['relation'] ?? null,
                ];
            }

            $allKeys[] = $key;
        }

        return [
            'resolved' => $resolved,
            'keys' => $allKeys,
        ];
    }

    /**
     * Apply filters to the query based on resolved filter configurations.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<int, array{type: FilterTypeEnum, column: string, value?: mixed, from?: string|null, to?: string|null, relation?: string|null}>  $filters
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            match ($filter['type']) {
                FilterTypeEnum::Select => $this->applySelectFilter($query, $filter['column'], $filter['value']),
                FilterTypeEnum::Text => $this->applyTextFilter($query, $filter['column'], $filter['value']),
                FilterTypeEnum::Number => $this->applyNumberFilter($query, $filter['column'], $filter['value']),
                FilterTypeEnum::DateRange => $this->applyDateRangeFilter($query, $filter['column'], $filter['from'] ?? null, $filter['to'] ?? null),
                FilterTypeEnum::NumberRange => $this->applyNumberRangeFilter($query, $filter['column'], $filter['from'] ?? null, $filter['to'] ?? null),
                FilterTypeEnum::Relation => $this->applyRelationFilter($query, $filter['relation'] ?? null, $filter['column'], $filter['value']),
                FilterTypeEnum::NullStatus => $this->applyNullStatusFilter($query, $filter['column'], $filter['value']),
                FilterTypeEnum::Boolean => $this->applyBooleanFilter($query, $filter['column'], $filter['value']),
            };
        }

        return $query;
    }

    /**
     * Apply SELECT filter (exact match).
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applySelectFilter(Builder $query, string $column, mixed $value): Builder
    {
        return $query->where($column, $value);
    }

    /**
     * Apply RELATION filter (exact match on a column of a related model).
     * Used for attributes that live on a relation rather than a column,
     * e.g. a Spatie role: `['type' => 'relation', 'relation' => 'roles', 'column' => 'name']`.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyRelationFilter(Builder $query, ?string $relation, string $column, mixed $value): Builder
    {
        if ($relation === null) {
            return $query;
        }

        return $query->whereHas($relation, function (Builder $relationQuery) use ($column, $value): void {
            $relationQuery->where($column, $value);
        });
    }

    /**
     * Apply NULL-STATUS filter on a nullable column (e.g. `email_verified_at`):
     * a truthy value → column IS NOT NULL, a falsy value → column IS NULL.
     * Driven by the same `1`/`0` values as the `boolean` filter.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyNullStatusFilter(Builder $query, string $column, mixed $value): Builder
    {
        return match ((string) $value) {
            '1', 'true' => $query->whereNotNull($column),
            '0', 'false' => $query->whereNull($column),
            default => $query,
        };
    }

    /**
     * Apply BOOLEAN filter on a real boolean column (e.g. `is_active`):
     * `1`/`true` → column = true, `0`/`false` → column = false.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyBooleanFilter(Builder $query, string $column, mixed $value): Builder
    {
        return $query->where($column, in_array((string) $value, ['1', 'true'], true));
    }

    /**
     * Apply TEXT filter (LIKE search).
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyTextFilter(Builder $query, string $column, mixed $value): Builder
    {
        return $query->where($column, 'like', "%{$value}%");
    }

    /**
     * Apply NUMBER filter (exact match).
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyNumberFilter(Builder $query, string $column, mixed $value): Builder
    {
        return $query->where($column, $value);
    }

    /**
     * Apply DATE RANGE filter.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyDateRangeFilter(Builder $query, string $column, ?string $from, ?string $to): Builder
    {
        if ($from !== null && $from !== '') {
            $query->whereDate($column, '>=', $from);
        }

        if ($to !== null && $to !== '') {
            $query->whereDate($column, '<=', $to);
        }

        return $query;
    }

    /**
     * Apply NUMBER RANGE filter (min/max, inclusive).
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyNumberRangeFilter(Builder $query, string $column, ?string $from, ?string $to): Builder
    {
        if ($from !== null && $from !== '') {
            $query->where($column, '>=', $from);
        }

        if ($to !== null && $to !== '') {
            $query->where($column, '<=', $to);
        }

        return $query;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<int, string>  $columns
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applySearch(Builder $query, string $search, array $columns): Builder
    {
        $query->where(function (Builder $q) use ($search, $columns): void {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });

        return $query;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applySorting(Builder $query, ?string $sortBy, string $direction = 'asc'): Builder
    {
        if ($sortBy === null) {
            return $query;
        }

        return $query->orderBy($sortBy, $direction);
    }

    /**
     * @param  class-string<DataTableConfig>  $resourceClass
     */
    private function resolveSortColumn(string $resourceClass, ?string $sortBy): string
    {
        if ($sortBy !== null && in_array($sortBy, $resourceClass::allowedSortColumns(), true)) {
            return $sortBy;
        }

        return $resourceClass::defaultSort();
    }
}
