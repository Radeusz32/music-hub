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

    /** @return list<array{value: string, label: string, color: string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Właściciel',
            self::Admin => 'Administrator',
            self::User => 'Użytkownik',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Owner => '#f472b6',
            self::Admin => '#38bdf8',
            self::User => '#94a3b8',
        };
    }
}
