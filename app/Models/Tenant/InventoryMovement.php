<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\Tenant\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 * @property-read int $inventory_record_id
 * @property-read InventoryMovementTypeEnum $type
 * @property-read int $quantity
 * @property-read int $quantity_before
 * @property-read int $quantity_after
 * @property-read string|null $sale_price
 * @property-read string|null $note
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 * @property-read InventoryRecord|null $inventoryRecord
 * @property-read User|null $user
 */
final class InventoryMovement extends Model
{
    /** @use HasFactory<InventoryMovementFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_record_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'sale_price',
        'note',
        'user_id',
    ];

    /**
     * @return array<string, mixed>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'inventory_record_id' => 'integer',
            'type' => InventoryMovementTypeEnum::class,
            'quantity' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'sale_price' => 'decimal:2',
            'note' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<InventoryRecord, $this> */
    public function inventoryRecord(): BelongsTo
    {
        return $this->belongsTo(InventoryRecord::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
