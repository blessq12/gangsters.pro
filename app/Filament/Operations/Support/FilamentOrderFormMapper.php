<?php

namespace App\Filament\Operations\Support;

use App\Application\Operations\Order\DTO\CreateAdminOrderDTO;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Order\Model\ORD_OrderItem;
use App\Support\Order\OrderStatusLabels;

final class FilamentOrderFormMapper
{
    public static function toFormState(ORD_Order $order): array
    {
        $order->loadMissing('items');
        $status = (string) $order->status;

        return [
            'id' => (string) $order->id,
            'status' => $status,
            'status_label' => OrderStatusLabels::statusLabel($status),
            'customer_name' => (string) ($order->customer_name ?? ''),
            'customer_phone' => (string) ($order->customer_phone ?? ''),
            'customer_email' => (string) ($order->customer_email ?? ''),
            'customer_address' => OrderStatusLabels::formatAddress($order->customer_address) ?? '',
            'delivery_method' => (string) ($order->delivery_method ?? ''),
            'delivery_address' => OrderStatusLabels::formatAddress($order->delivery_address) ?? '',
            'delivery_comment' => (string) ($order->delivery_comment ?? ''),
            'payment_method' => (string) ($order->payment_method ?? ''),
            'payment_status' => $order->payment_status,
            'subtotal' => (int) $order->subtotal,
            'discount_total' => (int) $order->discount_total,
            'delivery_fee' => (int) ($order->delivery_fee_kopecks ?? 0),
            'total' => (int) $order->total,
            'created_at' => $order->created_at?->toIso8601String() ?? '',
            'items' => $order->items
                ->map(static fn (ORD_OrderItem $item): array => [
                    'product_id' => (int) $item->product_original_id,
                    'product_label' => trim((string) $item->product_name)
                        .(filled($item->product_sku) ? ' ('.$item->product_sku.')' : ''),
                    'quantity' => (int) $item->quantity,
                    'row_total' => (int) $item->row_total,
                ])
                ->values()
                ->all(),
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
