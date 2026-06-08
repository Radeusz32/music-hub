<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use App\Models\Tenant\InventoryRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryRecord>
 */
final class InventoryRecordFactory extends Factory
{
    private const GENRES = [
        'Rock', 'Jazz', 'Metal', 'Pop', 'Blues',
        'Elektronika', 'Hip-Hop', 'Muzyka Klasyczna',
        'Folk', 'Reggae', 'Soul', 'Funk', 'Punk', 'Alternative',
    ];

    private const LABELS = [
        'Muza', 'Pronit', 'Tonpress', 'Polton', 'Savitor',
        'Koch International', 'PolyGram Polska', 'Sony Music Polska',
        'Universal Music Polska', 'Warner Music Poland',
        'EMI Music Poland', 'Metal Mind Productions', 'Mystic Production',
        'S.P. Records', 'Pomaton EMI',
    ];

    private const COUNTRIES = [
        'Polska', 'Polska', 'Polska', 'Polska',
        'Niemcy', 'Wielka Brytania', 'USA', 'Francja', 'Czechy',
    ];

    protected $model = InventoryRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake('pl_PL')->words(nb: fake()->numberBetween(1, 4), asText: true),
            'artist' => fake('pl_PL')->name(),
            'genre' => fake()->randomElement(self::GENRES),
            'format' => fake()->randomElement(DiscFormatEnum::cases())->value,
            'condition' => fake()->randomElement(DiscConditionEnum::cases())->value,
            'label' => fake()->optional(weight: 0.7)->randomElement(self::LABELS),
            'catalog_number' => fake()->optional(weight: 0.6)->bothify('??-####'),
            'barcode' => fake()->optional(weight: 0.5)->ean13(),
            'country' => fake()->optional(weight: 0.8)->randomElement(self::COUNTRIES),
            'year' => fake()->optional(weight: 0.8)->numberBetween(1950, 2025),
            'quantity' => fake()->numberBetween(0, 20),
            'purchase_price' => fake()->optional(weight: 0.8)->randomFloat(2, 10, 600),
            'sale_price' => fake()->optional(weight: 0.8)->randomFloat(2, 20, 900),
            'cover_image' => null,
            'notes' => fake()->optional(weight: 0.3)->randomElement([
                'Oryginalne pierwsze wydanie',
                'Okładka lekko przetarta na rogach',
                'Płyta bez zarysowań, stan kolekcjonerski',
                'Naklejka wytwórni na środku kompletna',
                'Brak wkładki, płyta VG+',
                'Wydanie eksportowe',
                'Nieużywana, folia oryginalna',
                'Drobne rysy, grają bez problemu',
                'Okładka wymaga konserwacji',
                'Rzadkie wydanie promocyjne',
            ]),
        ];
    }

    public function vinyl(): self
    {
        return $this->state(fn (array $attributes): array => [
            'format' => DiscFormatEnum::LP->value,
        ]);
    }

    public function cd(): self
    {
        return $this->state(fn (array $attributes): array => [
            'format' => DiscFormatEnum::CD->value,
        ]);
    }

    public function mint(): self
    {
        return $this->state(fn (array $attributes): array => [
            'condition' => DiscConditionEnum::Mint->value,
        ]);
    }

    public function outOfStock(): self
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => 0,
        ]);
    }
}
