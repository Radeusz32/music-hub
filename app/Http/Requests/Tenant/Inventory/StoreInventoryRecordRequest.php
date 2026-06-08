<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Inventory;

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreInventoryRecordRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'genre' => ['required', 'string', 'max:100'],
            'format' => ['required', 'string', Rule::enum(DiscFormatEnum::class)],
            'condition' => ['required', 'string', Rule::enum(DiscConditionEnum::class)],
            'label' => ['nullable', 'string', 'max:255'],
            'catalog_number' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'quantity' => ['required', 'integer', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tytuł jest wymagany.',
            'name.string' => 'Tytuł musi być tekstem.',
            'name.max' => 'Tytuł nie może przekraczać 255 znaków.',
            'artist.required' => 'Wykonawca jest wymagany.',
            'artist.string' => 'Wykonawca musi być tekstem.',
            'artist.max' => 'Wykonawca nie może przekraczać 255 znaków.',
            'genre.required' => 'Gatunek jest wymagany.',
            'genre.string' => 'Gatunek musi być tekstem.',
            'genre.max' => 'Gatunek nie może przekraczać 100 znaków.',
            'format.required' => 'Format jest wymagany.',
            'format.string' => 'Format musi być tekstem.',
            'format.enum' => 'Wybrany format jest nieprawidłowy.',
            'condition.required' => 'Stan płyty jest wymagany.',
            'condition.string' => 'Stan płyty musi być tekstem.',
            'condition.enum' => 'Wybrany stan płyty jest nieprawidłowy.',
            'label.string' => 'Wytwórnia musi być tekstem.',
            'label.max' => 'Wytwórnia nie może przekraczać 255 znaków.',
            'catalog_number.string' => 'Numer katalogowy musi być tekstem.',
            'catalog_number.max' => 'Numer katalogowy nie może przekraczać 100 znaków.',
            'barcode.string' => 'Kod kreskowy musi być tekstem.',
            'barcode.max' => 'Kod kreskowy nie może przekraczać 50 znaków.',
            'country.string' => 'Kraj musi być tekstem.',
            'country.max' => 'Kraj nie może przekraczać 100 znaków.',
            'year.integer' => 'Rok wydania musi być liczbą całkowitą.',
            'year.min' => 'Rok wydania nie może być wcześniejszy niż 1900.',
            'year.max' => 'Rok wydania nie może być późniejszy niż bieżący rok.',
            'quantity.required' => 'Ilość jest wymagana.',
            'quantity.integer' => 'Ilość musi być liczbą całkowitą.',
            'quantity.min' => 'Ilość nie może być ujemna.',
            'purchase_price.numeric' => 'Cena zakupu musi być liczbą.',
            'purchase_price.min' => 'Cena zakupu nie może być ujemna.',
            'purchase_price.max' => 'Cena zakupu nie może przekraczać 999 999,99 zł.',
            'sale_price.numeric' => 'Cena sprzedaży musi być liczbą.',
            'sale_price.min' => 'Cena sprzedaży nie może być ujemna.',
            'sale_price.max' => 'Cena sprzedaży nie może przekraczać 999 999,99 zł.',
            'notes.string' => 'Notatki muszą być tekstem.',
            'notes.max' => 'Notatki nie mogą przekraczać 5000 znaków.',
        ];
    }
}
