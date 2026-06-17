<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property string $id
 * @property string|null $company_name
 * @property bool $is_active
 */
final class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;

    protected $fillable = [
        'id',
        'company_name',
        'is_active',
        'tax_id',
        'regon',
        'krs_number',
        'company_email',
        'company_phone',
        'website',
        'street',
        'building_number',
        'apartment_number',
        'postal_code',
        'city',
        'country',
    ];

    /**
     * Columns stored as real table columns (not folded into the `data` JSON).
     *
     * @return array<int, string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'company_name',
            'is_active',
            'tax_id',
            'regon',
            'krs_number',
            'company_email',
            'company_phone',
            'website',
            'street',
            'building_number',
            'apartment_number',
            'postal_code',
            'city',
            'country',
        ];
    }

    /** @return array<string, string> */
    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Feature, $this> */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }
}
