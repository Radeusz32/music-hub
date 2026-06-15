<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Inventory;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInventorySaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'inventory_record_id' => ['required', 'integer', 'exists:inventory_records,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999999'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'inventory_record_id.required' => 'Wybór płyty jest wymagany.',
            'inventory_record_id.integer' => 'Identyfikator płyty jest nieprawidłowy.',
            'inventory_record_id.exists' => 'Wybrana płyta nie istnieje.',
            'quantity.required' => 'Ilość jest wymagana.',
            'quantity.integer' => 'Ilość musi być liczbą całkowitą.',
            'quantity.min' => 'Ilość musi wynosić co najmniej 1.',
            'quantity.max' => 'Ilość nie może przekraczać 999 999.',
            'sale_price.required' => 'Cena sprzedaży jest wymagana.',
            'sale_price.numeric' => 'Cena sprzedaży musi być liczbą.',
            'sale_price.min' => 'Cena sprzedaży nie może być ujemna.',
            'sale_price.max' => 'Cena sprzedaży nie może przekraczać 999 999,99 zł.',
            'note.string' => 'Notatka musi być tekstem.',
            'note.max' => 'Notatka nie może przekraczać 255 znaków.',
        ];
    }
}
