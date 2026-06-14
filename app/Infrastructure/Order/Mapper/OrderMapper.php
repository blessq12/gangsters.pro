<?php

namespace App\Infrastructure\Order\Mapper;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Enum\OrderClientKind;
use App\Domain\Order\Enum\OrderDeliveryMethod;
use App\Domain\Order\Enum\OrderPaymentMethod;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderGuestContact;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderLineSnapshot;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class OrderMapper
{
    public function toDomain(ORD_Order $row): Order
    {
        return Order::restore(
            id: OrderId::fromInt((int) $row->id),
            checkoutId: (string) $row->checkout_id,
            status: OrderStatus::from((string) $row->status),
            cart: $this->mapCartSnapshot(is_array($row->cart_snapshot) ? $row->cart_snapshot : []),
            client: $this->mapClientSnapshot(is_array($row->client_snapshot) ? $row->client_snapshot : []),
            delivery: $this->mapDeliverySnapshot(is_array($row->delivery_snapshot) ? $row->delivery_snapshot : []),
            payment: $this->mapPaymentSnapshot(is_array($row->payment_snapshot) ? $row->payment_snapshot : []),
            createdAt: new DateTimeImmutable((string) $row->created_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(Order $order): array
    {
        return [
            'id' => $order->hasId() ? $order->id()->value() : null,
            'checkout_id' => $order->checkoutId(),
            'status' => $order->status()->value,
            'client_id' => $order->client()->clientId(),
            'total_rubles' => $order->cart()->itemsTotal()->amountRubles(),
            'cart_snapshot' => $this->serializeCart($order->cart()),
            'client_snapshot' => $this->serializeClient($order->client()),
            'delivery_snapshot' => $this->serializeDelivery($order->delivery()),
            'payment_snapshot' => $this->serializePayment($order->payment()),
            'created_at' => $order->createdAt(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapCartSnapshot(array $payload): OrderCartSnapshot
    {
        $lines = [];

        foreach ($payload['lines'] ?? [] as $linePayload) {
            if (! is_array($linePayload)) {
                continue;
            }

            $lines[] = new OrderLineSnapshot(
                productId: (int) ($linePayload['product_id'] ?? 0),
                productName: (string) ($linePayload['product_name'] ?? ''),
                quantity: (int) ($linePayload['quantity'] ?? 0),
                unitPrice: Money::rubles((int) ($linePayload['unit_price_rubles'] ?? 0)),
                payload: is_array($linePayload['payload'] ?? null) ? $linePayload['payload'] : null,
            );
        }

        return OrderCartSnapshot::fromLines($lines);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapClientSnapshot(array $payload): OrderClientSnapshot
    {
        $kind = OrderClientKind::from((string) ($payload['kind'] ?? OrderClientKind::Guest->value));

        if ($kind === OrderClientKind::Registered) {
            return OrderClientSnapshot::registered(
                clientId: (int) ($payload['client_id'] ?? 0),
                name: isset($payload['name']) ? (string) $payload['name'] : null,
                phone: isset($payload['phone']) ? (string) $payload['phone'] : null,
                email: isset($payload['email']) ? (string) $payload['email'] : null,
            );
        }

        return OrderClientSnapshot::guest(
            new OrderGuestContact(
                name: (string) ($payload['name'] ?? ''),
                phone: (string) ($payload['phone'] ?? ''),
                email: isset($payload['email']) ? (string) $payload['email'] : null,
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapDeliverySnapshot(array $payload): OrderDeliverySnapshot
    {
        $addressPayload = $payload['address'] ?? null;

        return new OrderDeliverySnapshot(
            method: OrderDeliveryMethod::from((string) ($payload['method'] ?? OrderDeliveryMethod::Courier->value)),
            address: is_array($addressPayload)
                ? new OrderDeliveryAddress(
                    street: (string) ($addressPayload['street'] ?? ''),
                    house: (string) ($addressPayload['house'] ?? ''),
                    entrance: isset($addressPayload['entrance']) ? (string) $addressPayload['entrance'] : null,
                    apartment: isset($addressPayload['apartment']) ? (string) $addressPayload['apartment'] : null,
                )
                : null,
            comment: isset($payload['comment']) ? (string) $payload['comment'] : null,
            scheduledAt: isset($payload['scheduled_at']) ? (string) $payload['scheduled_at'] : null,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapPaymentSnapshot(array $payload): OrderPaymentSnapshot
    {
        return new OrderPaymentSnapshot(
            method: OrderPaymentMethod::from((string) ($payload['method'] ?? OrderPaymentMethod::Cash->value)),
            changeFromRubles: isset($payload['change_from_rubles'])
                ? (int) $payload['change_from_rubles']
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCart(OrderCartSnapshot $cart): array
    {
        return [
            'lines' => array_map(
                static fn (OrderLineSnapshot $line): array => [
                    'product_id' => $line->productId(),
                    'product_name' => $line->productName(),
                    'quantity' => $line->quantity(),
                    'unit_price_rubles' => $line->unitPrice()->amountRubles(),
                    'line_total_rubles' => $line->lineTotal()->amountRubles(),
                    'payload' => $line->payload(),
                ],
                $cart->lines(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeClient(OrderClientSnapshot $client): array
    {
        return [
            'kind' => $client->kind()->value,
            'client_id' => $client->clientId(),
            'name' => $client->name(),
            'phone' => $client->phone(),
            'email' => $client->email(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDelivery(OrderDeliverySnapshot $delivery): array
    {
        $address = $delivery->address();

        return [
            'method' => $delivery->method()->value,
            'address' => $address instanceof OrderDeliveryAddress
                ? [
                    'street' => $address->street(),
                    'house' => $address->house(),
                    'entrance' => $address->entrance(),
                    'apartment' => $address->apartment(),
                ]
                : null,
            'comment' => $delivery->comment(),
            'scheduled_at' => $delivery->scheduledAt(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePayment(OrderPaymentSnapshot $payment): array
    {
        return [
            'method' => $payment->method()->value,
            'change_from_rubles' => $payment->changeFromRubles(),
        ];
    }
}
