<?php

namespace App\Http\Requests\Checkout;

use App\Shared\Enum\DeliveryMethod;
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
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.house' => ['nullable', 'string', 'max:63'],
            'address.entrance' => ['nullable', 'string', 'max:63'],
            'address.apartment' => ['nullable', 'string', 'max:63'],
            'address.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'address.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'string', 'max:64'],
        ];
    }
}
