<?php

declare(strict_types=1);

namespace App\Transformers\Tenant\Inventory;

use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use App\Transformers\Tenant\Users\UserTransformer;
use App\Transformers\Transformer;

final class InventoryRecordTransformer extends Transformer
{
    protected array $defaultIncludes = [
        'user',
        'movements',
    ];

    protected array $availableIncludes = [
        //
    ];

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return ['user', 'user.roles:id,name', 'media'];
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(mixed $model): array
    {
        /** @var InventoryRecord $model */
        return [
            'id' => $model->id,
            'name' => $model->name,
            'artist' => $model->artist,
            'genre' => $model->genre,
            'format' => $model->format->value,
            'condition' => $model->condition->value,
            'label' => $model->label,
            'catalog_number' => $model->catalog_number,
            'barcode' => $model->barcode,
            'country' => $model->country,
            'year' => $model->year,
            'quantity' => $model->quantity,
            'purchase_price_per_unit' => $model->purchase_price_per_unit,
            'cover_image' => $model->getFirstMediaUrl('cover') ?: null,
            'notes' => $model->notes,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }

    /** @return array<string, mixed>|null */
    public function includeUser(mixed $model, mixed $_request = null): ?array
    {
        /** @var InventoryRecord $model */
        if (! $model->relationLoaded('user') || ! $model->user) {
            return null;
        }

        return (new UserTransformer())->transform($model->user);
    }

    /**
     * Stock-movement history. Only loaded on the detail page, so the list
     * view (where the relation is absent) returns an empty array.
     *
     * @return list<array<string, mixed>>
     */
    public function includeMovements(mixed $model, mixed $_request = null): array
    {
        /** @var InventoryRecord $model */
        if (! $model->relationLoaded('movements')) {
            return [];
        }

        $transformer = new InventoryMovementTransformer();

        return $model->movements
            ->map(fn (InventoryMovement $movement): array => $transformer->toArray($movement, request()))
            ->all();
    }
}
