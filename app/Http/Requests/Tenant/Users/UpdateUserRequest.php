<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Users;

use App\Enums\Tenant\RoleEnum;
use App\Rules\Tenant\PeselRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'phone' => ['nullable', 'string', 'max:32'],
            'street' => ['nullable', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:32'],
            'apartment_number' => ['nullable', 'string', 'max:32'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'city' => ['nullable', 'string', 'max:120'],
            'pesel' => ['nullable', new PeselRule($this->route('user')?->id)],
            'is_active' => ['required', 'boolean'],
            'role' => ['required', 'string', Rule::enum(RoleEnum::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Imię jest wymagane.',
            'first_name.string' => 'Imię musi być tekstem.',
            'first_name.max' => 'Imię nie może przekraczać 255 znaków.',
            'last_name.required' => 'Nazwisko jest wymagane.',
            'last_name.string' => 'Nazwisko musi być tekstem.',
            'last_name.max' => 'Nazwisko nie może przekraczać 255 znaków.',
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.email' => 'Adres e-mail jest nieprawidłowy.',
            'email.max' => 'Adres e-mail nie może przekraczać 255 znaków.',
            'email.unique' => 'Ten adres e-mail jest już zajęty.',
            'phone.max' => 'Numer telefonu nie może przekraczać 32 znaków.',
            'street.max' => 'Ulica nie może przekraczać 255 znaków.',
            'building_number.max' => 'Numer budynku nie może przekraczać 32 znaków.',
            'apartment_number.max' => 'Numer mieszkania nie może przekraczać 32 znaków.',
            'postal_code.max' => 'Kod pocztowy nie może przekraczać 16 znaków.',
            'city.max' => 'Miasto nie może przekraczać 120 znaków.',
            'is_active.required' => 'Status konta jest wymagany.',
            'is_active.boolean' => 'Status konta jest nieprawidłowy.',
            'role.required' => 'Rola jest wymagana.',
            'role.enum' => 'Wybrana rola jest nieprawidłowa.',
        ];
    }
}
