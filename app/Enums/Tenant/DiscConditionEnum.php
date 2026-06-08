<?php

declare(strict_types=1);

namespace App\Enums\Tenant;

enum DiscConditionEnum: string
{
    case Mint = 'M';
    case NearMint = 'NM';
    case VeryGoodPlus = 'VG+';
    case VeryGood = 'VG';
    case GoodPlus = 'G+';
    case Good = 'G';
    case Fair = 'F';
    case Poor = 'P';

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
            self::Mint => 'Idealny (M)',
            self::NearMint => 'Prawie Idealny (NM)',
            self::VeryGoodPlus => 'Bardzo Dobry+ (VG+)',
            self::VeryGood => 'Bardzo Dobry (VG)',
            self::GoodPlus => 'Dobry+ (G+)',
            self::Good => 'Dobry (G)',
            self::Fair => 'Dostateczny (F)',
            self::Poor => 'Słaby (P)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Mint => '#4ade80',
            self::NearMint => '#86efac',
            self::VeryGoodPlus => '#38bdf8',
            self::VeryGood => '#60a5fa',
            self::GoodPlus => '#facc15',
            self::Good => '#fb923c',
            self::Fair => '#f87171',
            self::Poor => '#6b7280',
        };
    }
}
