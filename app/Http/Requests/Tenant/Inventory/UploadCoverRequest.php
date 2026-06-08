<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Inventory;

use Illuminate\Foundation\Http\FormRequest;

final class UploadCoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cover' => ['required', 'file', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cover.required' => 'Wybierz plik zdjęcia.',
            'cover.image' => 'Plik musi być obrazem.',
            'cover.mimes' => 'Dozwolone formaty: JPEG, PNG, WebP.',
            'cover.max' => 'Plik nie może przekraczać 5 MB.',
        ];
    }
}
