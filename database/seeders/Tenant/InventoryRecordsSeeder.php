<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\InventoryRecord;
use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

final class InventoryRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->first();

        $classics = [
            [
                'name' => 'Sukces',
                'artist' => 'Czesław Niemen',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'NM',
                'label' => 'Muza',
                'catalog_number' => 'SXL 0436',
                'year' => 1967,
                'country' => 'Polska',
                'quantity' => 2,
                'purchase_price_per_unit' => '280.00',
                'notes' => 'Pierwsza edycja, okładka w bardzo dobrym stanie',
            ],
            [
                'name' => 'Na Drugim Brzegu Tęczy',
                'artist' => 'Breakout',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'VG+',
                'label' => 'Muza',
                'catalog_number' => 'SX 1038',
                'year' => 1969,
                'country' => 'Polska',
                'quantity' => 1,
                'purchase_price_per_unit' => '350.00',
            ],
            [
                'name' => 'Cegła',
                'artist' => 'Dżem',
                'genre' => 'Blues',
                'format' => 'LP',
                'condition' => 'NM',
                'label' => 'Tonpress',
                'catalog_number' => 'S-074',
                'year' => 1982,
                'country' => 'Polska',
                'quantity' => 3,
                'purchase_price_per_unit' => '120.00',
            ],
            [
                'name' => 'Nowe Sytuacje',
                'artist' => 'Republika',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'NM',
                'label' => 'Tonpress',
                'catalog_number' => 'S-068',
                'year' => 1983,
                'country' => 'Polska',
                'quantity' => 2,
                'purchase_price_per_unit' => '90.00',
            ],
            [
                'name' => 'Nocny Patrol',
                'artist' => 'Maanam',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'VG+',
                'label' => 'Pronit',
                'catalog_number' => 'SX 2192',
                'year' => 1983,
                'country' => 'Polska',
                'quantity' => 1,
                'purchase_price_per_unit' => '100.00',
            ],
            [
                'name' => 'Lady Pank',
                'artist' => 'Lady Pank',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'M',
                'label' => 'Pronit',
                'catalog_number' => 'SX 2212',
                'year' => 1983,
                'country' => 'Polska',
                'quantity' => 4,
                'purchase_price_per_unit' => '70.00',
            ],
            [
                'name' => 'Autobiografia',
                'artist' => 'Perfect',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'NM',
                'label' => 'Tonpress',
                'catalog_number' => 'S-116',
                'year' => 1985,
                'country' => 'Polska',
                'quantity' => 2,
                'purchase_price_per_unit' => '80.00',
            ],
            [
                'name' => 'Heavy Metal World',
                'artist' => 'TSA',
                'genre' => 'Metal',
                'format' => 'LP',
                'condition' => 'VG+',
                'label' => 'Tonpress',
                'catalog_number' => 'S-103',
                'year' => 1984,
                'country' => 'Polska',
                'quantity' => 1,
                'purchase_price_per_unit' => '140.00',
            ],
            [
                'name' => 'Śmierć Dyskotece',
                'artist' => 'Lombard',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'VG',
                'label' => 'Tonpress',
                'catalog_number' => 'S-065',
                'year' => 1982,
                'country' => 'Polska',
                'quantity' => 1,
                'purchase_price_per_unit' => '60.00',
                'notes' => 'Okładka lekko przetarta na rogach',
            ],
            [
                'name' => 'Nowy Horyzont',
                'artist' => 'SBB',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'VG+',
                'label' => 'Muza',
                'catalog_number' => 'SX 1489',
                'year' => 1975,
                'country' => 'Polska',
                'quantity' => 2,
                'purchase_price_per_unit' => '180.00',
            ],
            [
                'name' => 'Cień Wielkiej Góry',
                'artist' => 'Budka Suflera',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'NM',
                'label' => 'Tonpress',
                'catalog_number' => 'S-085',
                'year' => 1983,
                'country' => 'Polska',
                'quantity' => 3,
                'purchase_price_per_unit' => '75.00',
            ],
            [
                'name' => 'Cała Jesteś w Skowronkach',
                'artist' => 'Skaldowie',
                'genre' => 'Folk',
                'format' => 'LP',
                'condition' => 'VG',
                'label' => 'Muza',
                'catalog_number' => 'SXL 0746',
                'year' => 1970,
                'country' => 'Polska',
                'quantity' => 1,
                'purchase_price_per_unit' => '130.00',
            ],
            [
                'name' => 'De Mono',
                'artist' => 'De Mono',
                'genre' => 'Pop',
                'format' => 'LP',
                'condition' => 'NM',
                'label' => 'Tonpress',
                'catalog_number' => 'S-147',
                'year' => 1987,
                'country' => 'Polska',
                'quantity' => 2,
                'purchase_price_per_unit' => '55.00',
            ],
            [
                'name' => 'King',
                'artist' => 'T.Love',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'NM',
                'label' => 'Savitor',
                'catalog_number' => 'LP-019',
                'year' => 1990,
                'country' => 'Polska',
                'quantity' => 2,
                'purchase_price_per_unit' => '65.00',
            ],
            [
                'name' => 'To Właśnie My',
                'artist' => 'Czerwone Gitary',
                'genre' => 'Rock',
                'format' => 'LP',
                'condition' => 'G+',
                'label' => 'Pronit',
                'catalog_number' => 'XL 0389',
                'year' => 1967,
                'country' => 'Polska',
                'quantity' => 0,
                'purchase_price_per_unit' => '200.00',
                'notes' => 'Egzemplarz kolekcjonerski, okładka wymaga konserwacji',
            ],
        ];

        foreach ($classics as $data) {
            InventoryRecord::factory()->create(
                array_merge($data, ['user_id' => $owner?->id])
            );
        }

        InventoryRecord::factory()
            ->count(35)
            ->create(['user_id' => $owner?->id]);
    }
}
