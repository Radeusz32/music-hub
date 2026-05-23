<?php

declare(strict_types=1);

namespace App\Enums;

enum FeatureEnum: string
{
    case Inventory = 'inventory';
    case Trading = 'trading';
    case Analytics = 'analytics';
    case Integrations = 'integrations';
    case Users = 'users';
    case Settings = 'settings';

    public function label(): string
    {
        return match ($this) {
            self::Inventory => 'Magazyn',
            self::Trading => 'Giełdy',
            self::Analytics => 'Analityka',
            self::Integrations => 'Integracje',
            self::Users => 'Użytkownicy',
            self::Settings => 'Ustawienia',
        };
    }
}
