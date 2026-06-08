<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Inventory;

use Illuminate\Foundation\Http\FormRequest;

final class ImportInventoryRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.required' => 'Wybierz plik do importu.',
            'file.mimes' => 'Dozwolone formaty: XLSX, XLS, CSV.',
            'file.max' => 'Plik nie może przekraczać 10 MB.',
        ];
    }
}
