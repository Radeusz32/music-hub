<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use Carbon\CarbonInterface;
use Database\Factories\Tenant\InventoryRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $artist
 * @property-read string $genre
 * @property-read DiscFormatEnum $format
 * @property-read DiscConditionEnum $condition
 * @property-read string|null $label
 * @property-read string|null $catalog_number
 * @property-read string|null $barcode
 * @property-read string|null $country
 * @property-read int|null $year
 * @property-read int $quantity
 * @property-read string|null $purchase_price_per_unit
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 */
final class InventoryRecord extends Model implements HasMedia
{
    /** @use HasFactory<InventoryRecordFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'artist',
        'genre',
        'format',
        'condition',
        'label',
        'catalog_number',
        'barcode',
        'country',
        'year',
        'quantity',
        'purchase_price_per_unit',
        'notes',
        'user_id',
    ];

    /**
     * @return array<string, mixed>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'artist' => 'string',
            'genre' => 'string',
            'format' => DiscFormatEnum::class,
            'condition' => DiscConditionEnum::class,
            'label' => 'string',
            'catalog_number' => 'string',
            'barcode' => 'string',
            'country' => 'string',
            'year' => 'integer',
            'quantity' => 'integer',
            'purchase_price_per_unit' => 'decimal:2',
            'notes' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<InventoryMovement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
