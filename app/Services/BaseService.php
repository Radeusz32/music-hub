<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseService
{
    protected int $defaultPerPage = 15;

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $column => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $query->where($column, $value);
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
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $searchColumns
     * @return LengthAwarePaginator<\Illuminate\Database\Eloquent\Model>
     */
    protected function paginate(
        Builder $query,
        array $filters = [],
        ?string $search = null,
        array $searchColumns = [],
        ?string $sortBy = null,
        string $direction = 'asc',
        int $perPage = 0,
    ): LengthAwarePaginator {
        $this->applyFilters($query, $filters);

        if ($search !== null && $search !== '' && $searchColumns !== []) {
            $this->applySearch($query, $search, $searchColumns);
        }

        $this->applySorting($query, $sortBy, $direction);

        return $query->paginate($perPage > 0 ? $perPage : $this->defaultPerPage);
    }
}
