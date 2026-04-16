<?php

namespace App\Http\Requests\Order;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->request->remove('client_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isGuest = $this->user('sanctum') === null;

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'delivery_method' => ['required', 'string', Rule::enum(DeliveryMethod::class)],
            'delivery_address' => ['nullable', 'array', 'required_if:delivery_method,courier'],
            'delivery_address.street' => ['required_if:delivery_method,courier', 'string', 'max:255'],
            'delivery_address.house' => ['required_if:delivery_method,courier', 'string', 'max:63'],
            'delivery_address.entrance' => ['nullable', 'string', 'max:63'],
            'delivery_address.apartment' => ['nullable', 'string', 'max:63'],
            'delivery_comment' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
            'customer_name' => $isGuest
                ? ['required', 'string', 'max:255']
                : ['prohibited'],
            'customer_phone' => $isGuest
                ? ['required', 'string', 'max:32']
                : ['prohibited'],
            'customer_email' => $isGuest
                ? ['nullable', 'string', 'email', 'max:255']
                : ['prohibited'],
        ];
    }
}
