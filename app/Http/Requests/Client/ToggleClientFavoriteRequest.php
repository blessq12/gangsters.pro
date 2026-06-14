<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

final class ToggleClientFavoriteRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'string', 'max:64'],
        ];
    }
}
