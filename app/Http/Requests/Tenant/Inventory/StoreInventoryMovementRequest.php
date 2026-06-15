<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Inventory;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreInventoryMovementRequest extends FormRequest
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
            'type' => [
                'required',
                'string',
                Rule::in([
                    InventoryMovementTypeEnum::In->value,
                    InventoryMovementTypeEnum::Out->value,
                    InventoryMovementTypeEnum::Return->value,
                    InventoryMovementTypeEnum::Loss->value,
                ]),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:999999'],
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
            'type.required' => 'Typ ruchu jest wymagany.',
            'type.string' => 'Typ ruchu musi być tekstem.',
            'type.in' => 'Wybrany typ ruchu jest nieprawidłowy.',
            'quantity.required' => 'Ilość jest wymagana.',
            'quantity.integer' => 'Ilość musi być liczbą całkowitą.',
            'quantity.min' => 'Ilość musi wynosić co najmniej 1.',
            'quantity.max' => 'Ilość nie może przekraczać 999 999.',
            'note.string' => 'Powód musi być tekstem.',
            'note.max' => 'Powód nie może przekraczać 255 znaków.',
        ];
    }
}
