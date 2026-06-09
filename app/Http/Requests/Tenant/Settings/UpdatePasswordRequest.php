<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class UpdatePasswordRequest extends FormRequest
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
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Aktualne hasło jest wymagane.',
            'current_password.current_password' => 'Aktualne hasło jest nieprawidłowe.',
            'password.required' => 'Nowe hasło jest wymagane.',
            'password.confirmed' => 'Potwierdzenie hasła nie jest zgodne.',
            'password.min' => 'Hasło musi mieć co najmniej :min znaków.',
        ];
    }
}
