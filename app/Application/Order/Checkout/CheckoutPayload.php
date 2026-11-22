<?php

namespace App\Application\Order\Checkout;

use App\Application\Order\DTO\QuoteOrderDto;

/**
 * Один вход оформления для quote и create.
 * Подарок и комплекты с клиента не читаются: их считает OrderFormation.
 */
final class CheckoutPayload
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        $address = is_array($input['address'] ?? null) ? $input['address'] : null;
        if ($address !== null) {
            if (! isset($address['lat']) && isset($input['latitude'])) {
                $address['lat'] = $input['latitude'];
            }
            if (! isset($address['lng']) && isset($input['longitude'])) {
                $address['lng'] = $input['longitude'];
            }
            if (! isset($address['comment']) && isset($input['delivery_comment'])) {
                $address['comment'] = $input['delivery_comment'];
            }
        }

        $client = is_array($input['client'] ?? null) ? $input['client'] : null;
        if ($client !== null) {
            if (($client['kind'] ?? '') === 'registered') {
                $client['kind'] = 'client';
            }
            if (! isset($client['id']) && isset($client['client_id'])) {
                $client['id'] = $client['client_id'];
            }
        }

        return [
            'branch_id' => $input['branch_id'] ?? $input['branchId'] ?? null,
            'lines' => $input['lines'] ?? null,
            'delivery_method' => $input['delivery_method'] ?? $input['deliveryMethod'] ?? null,
            'address' => $address,
            'scheduled_at' => $input['scheduled_at'] ?? $input['scheduledAt'] ?? null,
            'client' => $client,
            'payment_method' => $input['payment_method'] ?? $input['paymentMethod'] ?? null,
            'change_from_rubles' => $input['change_from_rubles'] ?? $input['changeFrom'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function toDto(array $validated): QuoteOrderDto
    {
        $client = is_array($validated['client'] ?? null) ? $validated['client'] : [];

        return new QuoteOrderDto(
            branchId: (int) $validated['branch_id'],
            lines: array_map(
                static fn (array $line): array => [
                    'product_id' => (int) $line['product_id'],
                    'quantity' => (int) $line['quantity'],
                ],
                $validated['lines'],
            ),
            deliveryMethod: (string) $validated['delivery_method'],
            client: [
                'kind' => (string) ($client['kind'] ?? 'guest'),
                'id' => isset($client['id']) ? (int) $client['id'] : null,
                'name' => $client['name'] ?? null,
                'phone' => $client['phone'] ?? null,
                'email' => $client['email'] ?? null,
            ],
            address: is_array($validated['address'] ?? null) ? $validated['address'] : null,
            scheduledAt: isset($validated['scheduled_at']) ? (string) $validated['scheduled_at'] : null,
            paymentMethod: (string) $validated['payment_method'],
            changeFrom: isset($validated['change_from_rubles']) ? (int) $validated['change_from_rubles'] : null,
            giftProductId: null,
            complementSelections: [],
            promoCode: null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'min:1'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'delivery_method' => ['required', 'string', 'in:pickup,courier'],
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.house' => ['nullable', 'string', 'max:64'],
            'address.entrance' => ['nullable', 'string', 'max:64'],
            'address.floor' => ['nullable', 'string', 'max:64'],
            'address.apartment' => ['nullable', 'string', 'max:64'],
            'address.comment' => ['nullable', 'string', 'max:1000'],
            'address.lat' => ['nullable', 'numeric'],
            'address.lng' => ['nullable', 'numeric'],
            'scheduled_at' => ['nullable', 'string', 'max:64'],
            'client' => ['required', 'array'],
            'client.kind' => ['required', 'string', 'in:guest,client'],
            'client.id' => ['nullable', 'integer', 'min:1'],
            'client.name' => ['nullable', 'string', 'max:255'],
            'client.phone' => ['nullable', 'string', 'max:32'],
            'client.email' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'in:cash,card_courier,card_online'],
            'change_from_rubles' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
