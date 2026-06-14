<?php

namespace App\Http\Requests\Checkout;

use App\Domain\Checkout\Enum\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SetCheckoutPaymentRequest extends FormRequest
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
            'method' => ['required', 'string', Rule::in(PaymentMethod::placementValues())],
            'change_from_rubles' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
