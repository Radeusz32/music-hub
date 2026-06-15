<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Models\Tenant\InventoryMovement;
use App\Models\Tenant\InventoryRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
final class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            InventoryMovementTypeEnum::In,
            InventoryMovementTypeEnum::Out,
            InventoryMovementTypeEnum::Return,
            InventoryMovementTypeEnum::Loss,
        ]);

        $quantity = fake()->numberBetween(1, 8);
        $before = fake()->numberBetween(0, 20);
        $after = max(0, $before + ($type->sign() * $quantity));

        return [
            'inventory_record_id' => InventoryRecord::factory(),
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'note' => fake()->optional(weight: 0.4)->randomElement([
                'Dostawa od dystrybutora',
                'Sprzedaż w sklepie',
                'Zwrot od klienta',
                'Uszkodzenie podczas transportu',
                'Korekta po inwentaryzacji',
            ]),
            'user_id' => null,
        ];
    }
}
