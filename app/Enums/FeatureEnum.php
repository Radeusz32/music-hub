<?php

declare(strict_types=1);

namespace App\Enums;

enum FeatureEnum: string
{
    case Inventory = 'inventory';

    public function label(): string
    {
        return match ($this) {
            self::Inventory => 'Inventory',
        };
    }
}
