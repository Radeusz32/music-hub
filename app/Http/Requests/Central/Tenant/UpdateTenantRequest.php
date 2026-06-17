<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Tenant;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTenantRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:32'],
            'regon' => ['nullable', 'string', 'max:32'],
            'krs_number' => ['nullable', 'string', 'max:32'],
            'company_email' => ['nullable', 'string', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:32'],
            'apartment_number' => ['nullable', 'string', 'max:32'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Nazwa firmy jest wymagana.',
            'company_name.string' => 'Nazwa firmy musi być tekstem.',
            'company_name.max' => 'Nazwa firmy nie może przekraczać 255 znaków.',
            'tax_id.max' => 'NIP nie może przekraczać 32 znaków.',
            'regon.max' => 'REGON nie może przekraczać 32 znaków.',
            'krs_number.max' => 'Numer KRS nie może przekraczać 32 znaków.',
            'company_email.email' => 'Adres e-mail firmy jest nieprawidłowy.',
            'company_email.max' => 'Adres e-mail firmy nie może przekraczać 255 znaków.',
            'company_phone.max' => 'Numer telefonu nie może przekraczać 32 znaków.',
            'website.max' => 'Adres strony nie może przekraczać 255 znaków.',
            'street.max' => 'Ulica nie może przekraczać 255 znaków.',
            'building_number.max' => 'Numer budynku nie może przekraczać 32 znaków.',
            'apartment_number.max' => 'Numer lokalu nie może przekraczać 32 znaków.',
            'postal_code.max' => 'Kod pocztowy nie może przekraczać 16 znaków.',
            'city.max' => 'Miasto nie może przekraczać 120 znaków.',
            'country.max' => 'Kraj nie może przekraczać 120 znaków.',
        ];
    }
}
