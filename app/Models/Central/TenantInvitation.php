<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\TenantInvitationStatusEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property string $email
 * @property TenantInvitationStatusEnum $status
 * @property array<string, mixed>|null $company_data
 * @property array<string, mixed>|null $owner_data
 * @property string|null $tenant_id
 * @property CarbonInterface $expires_at
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 */
final class TenantInvitation extends Model
{
    protected $fillable = [
        'uuid',
        'email',
        'status',
        'company_data',
        'owner_data',
        'tenant_id',
        'expires_at',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast()
            && $this->status === TenantInvitationStatusEnum::Pending;
    }

    public function isPending(): bool
    {
        return $this->status === TenantInvitationStatusEnum::Pending;
    }

    public function isFilled(): bool
    {
        return $this->status === TenantInvitationStatusEnum::Filled;
    }

    public function isAccepted(): bool
    {
        return $this->status === TenantInvitationStatusEnum::Accepted;
    }

    /** @return array<string, mixed> */
    public function casts(): array
    {
        return [
            'status' => TenantInvitationStatusEnum::class,
            'company_data' => 'array',
            'owner_data' => 'array',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
