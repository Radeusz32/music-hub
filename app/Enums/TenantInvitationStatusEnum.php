<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantInvitationStatusEnum: string
{
    case Pending = 'PENDING';
    case Filled = 'FILLED';
    case Accepted = 'ACCEPTED';
    case Expired = 'EXPIRED';

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
            self::Pending => 'Oczekuje',
            self::Filled => 'Wypełnione',
            self::Accepted => 'Zaakceptowane',
            self::Expired => 'Wygasłe',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => '#fb923c',
            self::Filled => '#38bdf8',
            self::Accepted => '#4ade80',
            self::Expired => '#f87171',
        };
    }
}
