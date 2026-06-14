<?php

namespace App\Http\Requests\Checkout;

use App\Domain\Checkout\Enum\DeliveryMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SetCheckoutDeliveryRequest extends FormRequest
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
            'method' => ['required', 'string', Rule::enum(DeliveryMethod::class)],
            'address' => ['nullable', 'array', 'required_if:method,courier'],
            'address.street' => ['required_if:method,courier', 'string', 'max:255'],
            'address.house' => ['required_if:method,courier', 'string', 'max:63'],
            'address.entrance' => ['nullable', 'string', 'max:63'],
            'address.apartment' => ['nullable', 'string', 'max:63'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'string', 'max:64'],
        ];
    }
}
