<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCheckoutCartRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:0'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
