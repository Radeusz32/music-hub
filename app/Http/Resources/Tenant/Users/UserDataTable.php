<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\Users;

use App\Enums\Tenant\RoleEnum;
use App\Http\Resources\DataTableConfig;
use App\Models\Tenant\User;
use App\Transformers\Tenant\Users\UserTransformer;
use App\Transformers\Transformer;
use Illuminate\Database\Eloquent\Builder;

final class UserDataTable extends DataTableConfig
{
    /** @return Builder<User> */
    public static function baseQuery(): Builder
    {
        return User::query()->with(self::transformer()::eagerLoads());
    }

    /**
     * Plain (non-encrypted) columns searched with LIKE. The encrypted PII columns
     * are searched by exact value through blind indexes in {@see \App\Services\Tenant\Users\UserService::applySearch()}.
     *
     * @return list<string>
     */
    public static function searchableColumns(): array
    {
        return ['first_name', 'last_name', 'email'];
    }

    /** @return list<string> */
    public static function allowedSortColumns(): array
    {
        return ['first_name', 'last_name', 'email', 'is_active', 'email_verified_at', 'created_at', 'updated_at'];
    }

    /**
     * `role` filters through the Spatie `roles` relation and `email_verified_at`
     * uses a null-status strategy (verified / unverified) — both are resolved by
     * BaseService. `is_active` is a plain boolean column.
     *
     * @return array<string, array{column: string, type: string, relation?: string, options?: array<int, array{value: string, label: string}>}>
     */
    public static function filterableColumns(): array
    {
        return [
            'role' => [
                'column' => 'name',
                'type' => 'relation',
                'relation' => 'roles',
                'options' => RoleEnum::options(),
            ],
            'email_verified_at' => [
                'column' => 'email_verified_at',
                'type' => 'null-status',
            ],
            'is_active' => [
                'column' => 'is_active',
                'type' => 'boolean',
            ],
            'created_at' => [
                'column' => 'created_at',
                'type' => 'date-range',
            ],
            'updated_at' => [
                'column' => 'updated_at',
                'type' => 'date-range',
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
        return new UserTransformer();
    }
}
