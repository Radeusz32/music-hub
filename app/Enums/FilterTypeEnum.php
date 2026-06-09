<?php

declare(strict_types=1);

namespace App\Enums;

enum FilterTypeEnum: string
{
    case Select = 'select';
    case Text = 'text';
    case Number = 'number';
    case DateRange = 'date-range';
    case NumberRange = 'number-range';
    case Relation = 'relation';
    case NullStatus = 'null-status';
    case Boolean = 'boolean';

    public function isRange(): bool
    {
        return match ($this) {
            self::DateRange, self::NumberRange => true,
            default => false,
        };
    }
}
