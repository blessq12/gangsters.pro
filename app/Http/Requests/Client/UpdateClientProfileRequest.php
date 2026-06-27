<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateClientProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'consent_personal_data' => ['sometimes', 'boolean'],
            'consent_marketing' => ['sometimes', 'boolean'],
        ];
    }
}
