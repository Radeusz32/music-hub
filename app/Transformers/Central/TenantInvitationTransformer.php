<?php

declare(strict_types=1);

namespace App\Transformers\Central;

use App\Models\Central\TenantInvitation;
use App\Transformers\Transformer;

final class TenantInvitationTransformer extends Transformer
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(mixed $model): array
    {
        /** @var TenantInvitation $model */
        return [
            'id' => $model->id,
            'uuid' => $model->uuid,
            'email' => $model->email,
            'status' => $model->status->value,
            'status_label' => $model->status->label(),
            'status_color' => $model->status->color(),
            'company_data' => $model->company_data,
            'owner_data' => $model->owner_data,
            'tenant_id' => $model->tenant_id,
            'expires_at' => $model->expires_at,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }
}
