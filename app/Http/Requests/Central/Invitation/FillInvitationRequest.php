<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Invitation;

use App\Models\Central\Domain;
use App\Rules\Tenant\PeselRule;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class FillInvitationRequest extends FormRequest
{
    /**
     * Validation rules grouped per wizard step, so the same definition powers
     * both the per-step async validation endpoint and the final full submit.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function stepRules(): array
    {
        return [
            'domain' => [
                'domain' => [
                    'required',
                    'string',
                    'max:40',
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    self::uniqueDomainRule(),
                ],
            ],
            'company' => [
                'company_name' => ['required', 'string', 'max:255'],
                'tax_id' => ['nullable', 'string', 'max:20'],
                'regon' => ['nullable', 'string', 'max:20'],
                'krs_number' => ['nullable', 'string', 'max:20'],
                'company_email' => ['nullable', 'email', 'max:255'],
                'company_phone' => ['nullable', 'string', 'max:20'],
                'website' => ['nullable', 'url', 'max:255'],
            ],
            'address' => [
                'street' => ['nullable', 'string', 'max:255'],
                'building_number' => ['nullable', 'string', 'max:20'],
                'apartment_number' => ['nullable', 'string', 'max:20'],
                'postal_code' => ['nullable', 'string', 'max:10'],
                'city' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],
            ],
            'owner' => [
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'phone' => ['nullable', 'string', 'max:20'],
                'pesel' => ['nullable', 'string', new PeselRule(checkUniqueness: false)],
            ],
            'security' => [
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(...array_values(self::stepRules()));
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'domain.required' => 'Adres organizacji jest wymagany.',
            'domain.regex' => 'Adres może zawierać tylko małe litery, cyfry i myślniki.',
            'domain.max' => 'Adres może mieć maksymalnie 40 znaków.',
            'company_name.required' => 'Nazwa firmy jest wymagana.',
            'company_name.max' => 'Nazwa firmy może mieć maksymalnie 255 znaków.',
            'first_name.required' => 'Imię jest wymagane.',
            'last_name.required' => 'Nazwisko jest wymagane.',
            'password.required' => 'Hasło jest wymagane.',
            'password.min' => 'Hasło musi mieć co najmniej 8 znaków.',
            'password.confirmed' => 'Hasła muszą się zgadzać.',
        ];
    }

    private static function uniqueDomainRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $slug = Str::slug((string) $value);
            $baseDomain = config('app.base_domain');

            if (Domain::query()->where('domain', "{$slug}.{$baseDomain}")->exists()) {
                $fail('Ten adres organizacji jest już zajęty.');
            }
        };
    }
}
