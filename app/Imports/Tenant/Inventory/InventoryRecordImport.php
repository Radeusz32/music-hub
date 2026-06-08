<?php

declare(strict_types=1);

namespace App\Imports\Tenant\Inventory;

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use App\Models\Tenant\InventoryRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

final class InventoryRecordImport implements SkipsEmptyRows, ToModel, WithBatchInserts, WithMapping, WithStartRow, WithValidation
{
    public function startRow(): int
    {
        return 2;
    }

    /** @param array<int, mixed> $row */
    public function map($row): array
    {
        return [
            'name' => $row[0] ?? null,
            'artist' => $row[1] ?? null,
            'genre' => $row[2] ?? null,
            'format' => $row[3] ?? null,
            'condition' => $row[4] ?? null,
            'label' => $row[5] ?? null,
            'catalog_number' => $row[6] ?? null,
            'barcode' => $row[7] ?? null,
            'country' => $row[8] ?? null,
            'year' => $row[9] ?? null,
            'quantity' => $row[10] ?? null,
            'purchase_price' => $row[11] ?? null,
            'sale_price' => $row[12] ?? null,
            'notes' => $row[13] ?? null,
        ];
    }

    /** @param array<string, mixed> $row */
    public function model(array $row): InventoryRecord
    {
        return new InventoryRecord([
            'name' => $row['name'],
            'artist' => $row['artist'],
            'genre' => $row['genre'],
            'format' => $row['format'],
            'condition' => $row['condition'],
            'label' => $row['label'] ?: null,
            'catalog_number' => $row['catalog_number'] ?: null,
            'barcode' => $row['barcode'] ?: null,
            'country' => $row['country'] ?: null,
            'year' => isset($row['year']) && $row['year'] !== '' ? (int) $row['year'] : null,
            'quantity' => (int) $row['quantity'],
            'purchase_price' => isset($row['purchase_price']) && $row['purchase_price'] !== '' ? (float) $row['purchase_price'] : null,
            'sale_price' => isset($row['sale_price']) && $row['sale_price'] !== '' ? (float) $row['sale_price'] : null,
            'notes' => $row['notes'] ?: null,
            'user_id' => Auth::id(),
        ]);
    }

    public function batchSize(): int
    {
        return 100;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $formats = array_column(DiscFormatEnum::cases(), 'value');
        $conditions = array_column(DiscConditionEnum::cases(), 'value');

        return [
            'name' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'genre' => ['required', 'string', 'max:100'],
            'format' => ['required', Rule::in($formats)],
            'condition' => ['required', Rule::in($conditions)],
            'label' => ['nullable', 'string', 'max:255'],
            'catalog_number' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'quantity' => ['required', 'integer', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function customValidationAttributes(): array
    {
        return [
            'name' => 'Tytuł',
            'artist' => 'Wykonawca',
            'genre' => 'Gatunek',
            'format' => 'Format',
            'condition' => 'Stan',
            'label' => 'Wytwórnia',
            'catalog_number' => 'Nr katalogowy',
            'barcode' => 'Kod kreskowy',
            'country' => 'Kraj',
            'year' => 'Rok',
            'quantity' => 'Ilość',
            'purchase_price' => 'Cena zakupu',
            'sale_price' => 'Cena sprzedaży',
            'notes' => 'Notatki',
        ];
    }

    /** @return array<string, string> */
    public function customValidationMessages(): array
    {
        $formats = implode(', ', array_column(DiscFormatEnum::cases(), 'value'));
        $conditions = implode(', ', array_column(DiscConditionEnum::cases(), 'value'));

        return [
            'name.required' => 'Tytuł jest wymagany.',
            'artist.required' => 'Wykonawca jest wymagany.',
            'genre.required' => 'Gatunek jest wymagany.',
            'format.required' => 'Format jest wymagany.',
            'format.in' => "Nieprawidłowy format. Dozwolone wartości: {$formats}.",
            'condition.required' => 'Stan jest wymagany.',
            'condition.in' => "Nieprawidłowy stan. Dozwolone wartości: {$conditions}.",
            'year.integer' => 'Rok musi być liczbą całkowitą.',
            'year.min' => 'Rok nie może być wcześniejszy niż 1900.',
            'year.max' => 'Rok nie może być późniejszy niż bieżący rok.',
            'quantity.required' => 'Ilość jest wymagana.',
            'quantity.integer' => 'Ilość musi być liczbą całkowitą.',
            'quantity.min' => 'Ilość nie może być ujemna.',
        ];
    }
}
