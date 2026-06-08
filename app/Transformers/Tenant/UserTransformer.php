<?php

declare(strict_types=1);

namespace App\Transformers\Tenant;

use App\Models\Tenant\User;
use App\Transformers\Transformer;

final class UserTransformer extends Transformer
{
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
        /** @var User $model */
        return [
            'id' => $model->id,
            'name' => $model->name,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'email' => $model->email,
        ];
    }
}
