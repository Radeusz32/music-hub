<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Enums\FilterTypeEnum;
use App\Enums\TenantInvitationStatusEnum;
use App\Http\Resources\DataTableConfig;
use App\Models\Central\TenantInvitation;
use App\Transformers\Central\TenantInvitationTransformer;
use App\Transformers\Transformer;
use Illuminate\Database\Eloquent\Builder;

final class TenantInvitationDataTable extends DataTableConfig
{
    /** @return Builder<TenantInvitation> */
    public static function baseQuery(): Builder
    {
        return TenantInvitation::query();
    }

    /** @return list<string> */
    public static function searchableColumns(): array
    {
        return ['email'];
    }

    /** @return list<string> */
    public static function allowedSortColumns(): array
    {
        return ['email', 'status', 'expires_at', 'created_at'];
    }

    /**
     * @return array<string, array{column: string, type: FilterTypeEnum, options?: array<int, array{value: string, label: string}>}>
     */
    public static function filterableColumns(): array
    {
        return [
            'status' => [
                'column' => 'status',
                'type' => FilterTypeEnum::Select,
                'options' => array_map(
                    fn (array $opt): array => ['value' => $opt['value'], 'label' => $opt['label']],
                    TenantInvitationStatusEnum::options(),
                ),
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
        return new TenantInvitationTransformer();
    }
}
