<?php

namespace App\Infrastructure\Order\Mapper;

use App\Domain\Order\Entity\Order;
use App\Infrastructure\Order\Model\ORD_Order;
use DateTimeImmutable;

final class OrderMapper
{
    public function toDomain(ORD_Order $row): Order
    {
        return Order::restore(
            id: (int) $row->id,
            source: (string) ($row->source ?? 'site'),
            checkoutId: $row->checkout_id !== null ? (string) $row->checkout_id : null,
            partnerCode: isset($row->partner_code) && (string) $row->partner_code !== ''
                ? (string) $row->partner_code
                : null,
            externalOrderId: isset($row->external_order_id) && (string) $row->external_order_id !== ''
                ? (string) $row->external_order_id
                : null,
            status: (string) $row->status,
            cart: is_array($row->cart_snapshot) ? $row->cart_snapshot : [],
            client: is_array($row->client_snapshot) ? $row->client_snapshot : [],
            delivery: is_array($row->delivery_snapshot) ? $row->delivery_snapshot : [],
            payment: is_array($row->payment_snapshot) ? $row->payment_snapshot : [],
            createdAt: new DateTimeImmutable((string) $row->created_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(Order $order): array
    {
        return [
            'id' => $order->hasId() ? $order->id() : null,
            'source' => $order->source(),
            'checkout_id' => $order->checkoutId(),
            'partner_code' => $order->partnerCode(),
            'external_order_id' => $order->externalOrderId(),
            'status' => $order->status(),
            'client_id' => $order->clientId(),
            'total_rubles' => $order->totalRubles(),
            'cart_snapshot' => $order->cart(),
            'client_snapshot' => $order->client(),
            'delivery_snapshot' => $order->delivery(),
            'payment_snapshot' => $order->payment(),
            'created_at' => $order->createdAt(),
        ];
    }
}
