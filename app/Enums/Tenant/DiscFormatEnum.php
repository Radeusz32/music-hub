<?php

declare(strict_types=1);

namespace App\Enums\Tenant;

enum DiscFormatEnum: string
{
    case LP = 'LP';
    case EP = 'EP';
    case Single = 'Single';
    case DoubleLP = 'Double LP';
    case CD = 'CD';
    case CDSingle = 'CD Single';
    case DVD = 'DVD';
    case BluRay = 'Blu-Ray';
    case Cassette = 'Cassette';
    case VHS = 'VHS';
    case Digital = 'Digital';
    case Box = 'Box Set';

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
            self::LP => 'LP (Winyl 12")',
            self::EP => 'EP (Winyl 7"/10")',
            self::Single => 'Single (7")',
            self::DoubleLP => 'Podwójny LP',
            self::CD => 'CD',
            self::CDSingle => 'CD Single',
            self::DVD => 'DVD',
            self::BluRay => 'Blu-Ray',
            self::Cassette => 'Kaseta',
            self::VHS => 'VHS',
            self::Digital => 'Cyfrowy',
            self::Box => 'Zestaw (Box Set)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LP, self::DoubleLP => '#f97316',
            self::EP, self::Single => '#fb923c',
            self::CD, self::CDSingle => '#38bdf8',
            self::DVD, self::BluRay => '#a78bfa',
            self::Cassette, self::VHS => '#4ade80',
            self::Digital => '#60a5fa',
            self::Box => '#f472b6',
        };
    }
}
