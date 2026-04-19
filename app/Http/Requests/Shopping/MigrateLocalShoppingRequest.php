<?php

namespace App\Http\Requests\Shopping;

use Illuminate\Foundation\Http\FormRequest;

final class MigrateLocalShoppingRequest extends FormRequest
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
            'cart_items' => ['nullable', 'array'],
            'cart_items.*.productId' => ['sometimes', 'integer', 'min:1'],
            'cart_items.*.product_id' => ['sometimes', 'integer', 'min:1'],
            'cart_items.*.qty' => ['sometimes', 'integer', 'min:1'],
            'cart_items.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'favorite_product_ids' => ['nullable', 'array'],
            'favorite_product_ids.*' => ['integer', 'min:1'],
            'checkout_draft' => ['nullable', 'array'],
        ];
    }
}
