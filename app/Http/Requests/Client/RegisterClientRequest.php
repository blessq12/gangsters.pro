<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterClientRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'consent_personal_data' => ['required', 'boolean'],
            'consent_marketing' => ['sometimes', 'boolean'],
        ];
    }
}
