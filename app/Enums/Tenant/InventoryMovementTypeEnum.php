<?php

declare(strict_types=1);

namespace App\Enums\Tenant;

enum InventoryMovementTypeEnum: string
{
    case Initial = 'initial';
    case In = 'in';
    case Out = 'out';
    case Return = 'return';
    case Loss = 'loss';
    case Correction = 'correction';

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

    /**
     * Types a user may pick manually in the warehouse-movement form.
     * `Initial` and `Correction` are produced automatically by the system.
     *
     * @return list<array{value: string, label: string, color: string}>
     */
    public static function manualOptions(): array
    {
        return array_map(
            fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
            ],
            [self::In, self::Out, self::Return, self::Loss],
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Stan początkowy',
            self::In => 'Przyjęcie',
            self::Out => 'Wydanie',
            self::Return => 'Zwrot',
            self::Loss => 'Ubytek',
            self::Correction => 'Korekta',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Initial => '#94a3b8',
            self::In => '#4ade80',
            self::Out => '#f87171',
            self::Return => '#38bdf8',
            self::Loss => '#fb923c',
            self::Correction => '#facc15',
        };
    }

    /**
     * Stock direction this type applies when recorded manually:
     * +1 increases stock, -1 decreases it.
     */
    public function sign(): int
    {
        return match ($this) {
            self::Initial, self::In, self::Return, self::Correction => 1,
            self::Out, self::Loss => -1,
        };
    }
}
