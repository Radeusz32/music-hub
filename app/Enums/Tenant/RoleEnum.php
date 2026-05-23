<?php

declare(strict_types=1);

namespace App\Enums\Tenant;

enum RoleEnum: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case User = 'user';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
