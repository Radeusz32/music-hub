<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Users;

use Illuminate\Foundation\Http\FormRequest;

final class BulkDestroyUsersRequest extends FormRequest
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
            'ids.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Nie wybrano żadnych użytkowników do usunięcia.',
            'ids.array' => 'Lista użytkowników jest nieprawidłowa.',
            'ids.min' => 'Nie wybrano żadnych użytkowników do usunięcia.',
            'ids.*.integer' => 'Identyfikatory użytkowników są nieprawidłowe.',
        ];
    }
}
