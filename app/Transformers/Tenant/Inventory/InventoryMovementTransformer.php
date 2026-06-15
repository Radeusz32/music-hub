<?php

declare(strict_types=1);

namespace App\Transformers\Tenant\Inventory;

use App\Models\Tenant\InventoryMovement;
use App\Transformers\Tenant\Users\UserTransformer;
use App\Transformers\Transformer;

final class InventoryMovementTransformer extends Transformer
{
    protected array $defaultIncludes = [
        'inventoryRecord',
        'user',
    ];

    protected array $availableIncludes = [
        //
    ];

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return ['inventoryRecord:id,name,artist', 'user', 'user.roles:id,name'];
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(mixed $model): array
    {
        /** @var InventoryMovement $model */
        return [
            'id' => $model->id,
            'inventory_record_id' => $model->inventory_record_id,
            'type' => $model->type->value,
            'quantity' => $model->quantity,
            'quantity_before' => $model->quantity_before,
            'quantity_after' => $model->quantity_after,
            'delta' => $model->quantity_after - $model->quantity_before,
            'note' => $model->note,
            'created_at' => $model->created_at,
        ];
    }

    /** @return array<string, mixed>|null */
    public function includeInventoryRecord(mixed $model, mixed $_request = null): ?array
    {
        /** @var InventoryMovement $model */
        if (! $model->relationLoaded('inventoryRecord') || ! $model->inventoryRecord) {
            return null;
        }

        return [
            'id' => $model->inventoryRecord->id,
            'name' => $model->inventoryRecord->name,
            'artist' => $model->inventoryRecord->artist,
        ];
    }

    /** @return array<string, mixed>|null */
    public function includeUser(mixed $model, mixed $_request = null): ?array
    {
        /** @var InventoryMovement $model */
        if (! $model->relationLoaded('user') || ! $model->user) {
            return null;
        }

        return (new UserTransformer())->transform($model->user);
    }
}
