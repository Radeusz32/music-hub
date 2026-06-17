<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Feature;

use App\Enums\FeatureEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ToggleFeatureRequest extends FormRequest
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
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'feature' => ['required', Rule::enum(FeatureEnum::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Sklep jest wymagany.',
            'tenant_id.exists' => 'Wybrany sklep nie istnieje.',
            'feature.required' => 'Moduł jest wymagany.',
            'feature.Illuminate\Validation\Rules\Enum' => 'Wybrany moduł jest nieprawidłowy.',
        ];
    }
}
