<?php

namespace App\Http\Requests\OrderDraft;

use App\Shared\Enum\DeliveryMethod;
use App\Shared\Enum\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PlaceOrderRequest extends FormRequest
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
            'client_request_id' => ['required', 'uuid'],
            'cart' => ['required', 'array'],
            'cart.lines' => ['present', 'array'],
            'cart.lines.*.product_id' => ['required', 'integer', 'min:1'],
            'cart.lines.*.quantity' => ['required', 'integer', 'min:1'],
            'cart.lines.*.payload' => ['nullable', 'array'],
            'cart.selected_gift_product_id' => ['nullable', 'integer', 'min:1'],
            'client' => ['required', 'array'],
            'client.client_id' => ['nullable', 'integer', 'min:1'],
            'client.name' => ['required_without:client.client_id', 'nullable', 'string', 'max:255'],
            'client.phone' => ['required_without:client.client_id', 'nullable', 'string', 'max:20'],
            'client.email' => ['nullable', 'string', 'max:255'],
            'delivery' => ['required', 'array'],
            'delivery.method' => ['required', 'string', Rule::enum(DeliveryMethod::class)],
            'delivery.address' => ['nullable', 'array'],
            'delivery.address.street' => ['nullable', 'string', 'max:255'],
            'delivery.address.house' => ['nullable', 'string', 'max:63'],
            'delivery.address.entrance' => ['nullable', 'string', 'max:63'],
            'delivery.address.apartment' => ['nullable', 'string', 'max:63'],
            'delivery.address.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'delivery.address.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery.comment' => ['nullable', 'string', 'max:2000'],
            'delivery.scheduled_at' => ['nullable', 'string', 'max:64'],
            'payment' => ['required', 'array'],
            'payment.method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
            'payment.change_from_rubles' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
