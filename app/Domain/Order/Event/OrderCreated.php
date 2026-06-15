<?php

namespace App\Domain\Order\Event;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Enum\OrderSource;
use App\Domain\Order\ValueObject\OrderAggregatorReference;
use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;
use DateTimeImmutable;

/**
 * Заказ впервые сохранён в хранилище — точка подключения исходящих интеграций.
 */
final readonly class OrderCreated
{
    public function __construct(
        private OrderId $orderId,
        private OrderSource $source,
        private ?string $checkoutId,
        private ?OrderAggregatorReference $aggregatorReference,
        private OrderCartSnapshot $cart,
        private OrderClientSnapshot $client,
        private OrderDeliverySnapshot $delivery,
        private OrderPaymentSnapshot $payment,
        private DateTimeImmutable $occurredAt,
    ) {}

    public static function fromOrder(Order $order): self
    {
        if (! $order->hasId()) {
            throw new \LogicException('Событие OrderCreated можно построить только после сохранения заказа.');
        }

        return new self(
            orderId: $order->id(),
            source: $order->source(),
            checkoutId: $order->checkoutId(),
            aggregatorReference: $order->aggregatorReference(),
            cart: $order->cart(),
            client: $order->client(),
            delivery: $order->delivery(),
            payment: $order->payment(),
            occurredAt: $order->createdAt(),
        );
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function source(): OrderSource
    {
        return $this->source;
    }

    public function checkoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function aggregatorReference(): ?OrderAggregatorReference
    {
        return $this->aggregatorReference;
    }

    public function cart(): OrderCartSnapshot
    {
        return $this->cart;
    }

    public function client(): OrderClientSnapshot
    {
        return $this->client;
    }

    public function delivery(): OrderDeliverySnapshot
    {
        return $this->delivery;
    }

    public function payment(): OrderPaymentSnapshot
    {
        return $this->payment;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
