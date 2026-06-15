<?php

namespace App\Http\Requests\OrderDraft;

use App\Shared\Enum\DeliveryMethod;
use App\Shared\Enum\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PreviewOrderDraftRequest extends FormRequest
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
            'cart' => ['required', 'array'],
            'cart.lines' => ['present', 'array'],
            'cart.lines.*.product_id' => ['required', 'integer', 'min:1'],
            'cart.lines.*.quantity' => ['required', 'integer', 'min:1'],
            'cart.lines.*.payload' => ['nullable', 'array'],
            'cart.selected_gift_product_id' => ['nullable', 'integer', 'min:1'],
            'client' => ['nullable', 'array'],
            'client.client_id' => ['nullable', 'integer', 'min:1'],
            'client.name' => ['nullable', 'string', 'max:255'],
            'client.phone' => ['nullable', 'string', 'max:32'],
            'client.email' => ['nullable', 'string', 'max:255'],
            'delivery' => ['nullable', 'array'],
            'delivery.method' => ['nullable', 'string', Rule::enum(DeliveryMethod::class)],
            'delivery.address' => ['nullable', 'array'],
            'delivery.address.street' => ['nullable', 'string', 'max:255'],
            'delivery.address.house' => ['nullable', 'string', 'max:63'],
            'delivery.address.entrance' => ['nullable', 'string', 'max:63'],
            'delivery.address.apartment' => ['nullable', 'string', 'max:63'],
            'delivery.address.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'delivery.address.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery.comment' => ['nullable', 'string', 'max:2000'],
            'delivery.scheduled_at' => ['nullable', 'string', 'max:64'],
            'payment' => ['nullable', 'array'],
            'payment.method' => ['nullable', 'string', Rule::enum(PaymentMethod::class)],
            'payment.change_from_rubles' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
