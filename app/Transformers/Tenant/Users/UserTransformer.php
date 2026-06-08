<?php

declare(strict_types=1);

namespace App\Transformers\Tenant\Users;

use App\Models\Tenant\User;
use App\Transformers\Transformer;

final class UserTransformer extends Transformer
{
    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return ['roles:id,name'];
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(mixed $model): array
    {
        /** @var User $model */
        $roles = $model->getRoleNames();

        return [
            'id' => $model->id,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'name' => $model->name,
            'email' => $model->email,
            'phone' => $model->phone,
            'street' => $model->street,
            'building_number' => $model->building_number,
            'apartment_number' => $model->apartment_number,
            'postal_code' => $model->postal_code,
            'city' => $model->city,
            'pesel' => $model->pesel,
            'is_active' => $model->is_active,
            'role' => $roles->first(),
            'roles' => $roles->values()->all(),
            'email_verified_at' => $model->email_verified_at,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }
}
