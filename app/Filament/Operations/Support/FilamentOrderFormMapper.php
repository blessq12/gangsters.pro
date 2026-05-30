<?php

namespace App\Filament\Operations\Support;

use App\Application\Operations\Order\DTO\CreateAdminOrderDTO;
use App\Support\Order\OrderStatusLabels;

final class FilamentOrderFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        $customer = $detail['customer'] ?? [];
        $delivery = $detail['delivery'] ?? [];
        $payment = $detail['payment'] ?? [];

        return [
            'id' => $detail['id'] ?? '',
            'status' => $detail['status'] ?? '',
            'status_label' => OrderStatusLabels::statusLabel((string) ($detail['status'] ?? '')),
            'customer_name' => $customer['name'] ?? '',
            'customer_phone' => $customer['phone'] ?? '',
            'customer_email' => $customer['email'] ?? '',
            'customer_address' => OrderStatusLabels::formatAddress($customer['address'] ?? null) ?? '',
            'delivery_method' => $delivery['method'] ?? '',
            'delivery_address' => OrderStatusLabels::formatAddress($delivery['address'] ?? null) ?? '',
            'delivery_comment' => $delivery['comment'] ?? '',
            'payment_method' => $payment['method'] ?? '',
            'payment_status' => $payment['status'] ?? '',
            'subtotal' => $detail['subtotal'] ?? 0,
            'discount_total' => $detail['discount_total'] ?? 0,
            'delivery_fee' => $detail['delivery_fee'] ?? 0,
            'total' => $detail['total'] ?? 0,
            'created_at' => $detail['created_at'] ?? '',
            'items' => array_map(
                static fn (array $item): array => [
                    'product_id' => (int) ($item['product_original_id'] ?? 0),
                    'product_label' => ($item['product']['name'] ?? '').' ('.($item['product']['sku'] ?? '').')',
                    'quantity' => (int) ($item['quantity'] ?? 0),
                    'row_total' => $item['row_total'] ?? 0,
                ],
                $detail['items'] ?? [],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{product_id: int, quantity: int}>
     */
    public static function toOrderItems(array $data): array
    {
        $items = [];

        foreach ($data['items'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $productId = (int) ($row['product_id'] ?? 0);
            $quantity = (int) ($row['quantity'] ?? 0);

            if ($productId < 1 || $quantity < 1) {
                continue;
            }

            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toCreateAdminOrderDto(array $data): CreateAdminOrderDTO
    {
        $address = array_filter([
            'street' => filled($data['delivery_street'] ?? null) ? (string) $data['delivery_street'] : null,
            'house' => filled($data['delivery_house'] ?? null) ? (string) $data['delivery_house'] : null,
            'entrance' => filled($data['delivery_entrance'] ?? null) ? (string) $data['delivery_entrance'] : null,
            'apartment' => filled($data['delivery_apartment'] ?? null) ? (string) $data['delivery_apartment'] : null,
        ]);

        return new CreateAdminOrderDTO(
            clientId: filled($data['client_id'] ?? null) ? (int) $data['client_id'] : null,
            guestCustomerName: filled($data['guest_customer_name'] ?? null) ? (string) $data['guest_customer_name'] : null,
            guestCustomerPhone: filled($data['guest_customer_phone'] ?? null) ? (string) $data['guest_customer_phone'] : null,
            guestCustomerEmail: filled($data['guest_customer_email'] ?? null) ? (string) $data['guest_customer_email'] : null,
            items: self::toOrderItems($data),
            deliveryMethod: (string) ($data['delivery_method'] ?? 'courier'),
            deliveryAddress: $address === [] ? null : $address,
            deliveryComment: filled($data['delivery_comment'] ?? null) ? (string) $data['delivery_comment'] : null,
            paymentMethod: (string) ($data['payment_method'] ?? 'cash'),
        );
    }
}
