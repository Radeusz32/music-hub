<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class BulkDestroyTenantsRequest extends FormRequest
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
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'exists:tenants,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Wybierz przynajmniej jeden sklep.',
            'ids.array' => 'Nieprawidłowy format listy identyfikatorów.',
            'ids.min' => 'Wybierz przynajmniej jeden sklep.',
            'ids.*.string' => 'Identyfikator sklepu jest nieprawidłowy.',
            'ids.*.exists' => 'Wybrany sklep nie istnieje.',
        ];
    }
}
