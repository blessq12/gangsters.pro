<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

final class MergeGuestFavoritesRequest extends FormRequest
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
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.price_rub' => ['nullable', 'numeric', 'min:0'],
            'items.*.weight' => ['nullable', 'string', 'max:64'],
        ];
    }
}
