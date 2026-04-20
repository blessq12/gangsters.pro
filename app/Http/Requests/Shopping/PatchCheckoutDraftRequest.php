<?php

namespace App\Http\Requests\Shopping;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PatchCheckoutDraftRequest extends FormRequest
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
            'delivery_info' => ['nullable', 'array'],
            'delivery_info.method' => ['nullable', 'string', Rule::enum(DeliveryMethod::class)],
            'delivery_info.address' => ['nullable', 'array'],
            'delivery_info.comment' => ['nullable', 'string', 'max:2000'],
            'delivery_info.scheduled_at' => ['nullable', 'string', 'max:64'],
            'payment_info' => ['nullable', 'array'],
            'payment_info.method' => ['nullable', 'string', Rule::enum(PaymentMethod::class)],
            'payment_info.change_from' => ['nullable', 'numeric'],
            'guest_contact' => ['nullable', 'array'],
            'guest_contact.name' => ['nullable', 'string', 'max:255'],
            'guest_contact.phone' => ['nullable', 'string', 'max:32'],
            'guest_contact.email' => ['nullable', 'string', 'email', 'max:255'],
            'customer_comment' => ['nullable', 'string', 'max:2000'],
            'promotions' => ['nullable', 'array'],
            'promotions.free_roll_gift_product_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
