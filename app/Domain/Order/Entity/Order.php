<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\Enum\OrderSource;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Exception\OrderInvariantViolation;
use App\Domain\Order\ValueObject\OrderAggregatorReference;
use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;
use DateTimeImmutable;

/**
 * Агрегат заказа — неизменяемый снимок подтверждённого оформления или ingress-заказа агрегатора.
 */
final class Order
{
    private function __construct(
        private ?OrderId $id,
        private readonly OrderSource $source,
        private readonly ?string $checkoutId,
        private readonly ?OrderAggregatorReference $aggregatorReference,
        private readonly OrderStatus $status,
        private readonly OrderCartSnapshot $cart,
        private readonly OrderClientSnapshot $client,
        private readonly OrderDeliverySnapshot $delivery,
        private readonly OrderPaymentSnapshot $payment,
        private readonly DateTimeImmutable $createdAt,
    ) {}

    public static function fromCheckoutSnapshot(
        string $checkoutId,
        OrderCartSnapshot $cart,
        OrderClientSnapshot $client,
        OrderDeliverySnapshot $delivery,
        OrderPaymentSnapshot $payment,
        DateTimeImmutable $createdAt,
    ): self {
        if ($checkoutId === '') {
            throw OrderInvariantViolation::invalidCheckoutReference();
        }

        if ($cart->lines() === []) {
            throw OrderInvariantViolation::emptyCart();
        }

        return new self(
            id: null,
            source: OrderSource::Site,
            checkoutId: $checkoutId,
            aggregatorReference: null,
            status: OrderStatus::New,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
        );
    }

    public static function fromIngressSnapshot(
        OrderAggregatorReference $aggregatorReference,
        OrderCartSnapshot $cart,
        OrderClientSnapshot $client,
        OrderDeliverySnapshot $delivery,
        OrderPaymentSnapshot $payment,
        DateTimeImmutable $createdAt,
    ): self {
        if ($aggregatorReference->partnerCode() === '' || $aggregatorReference->externalOrderId() === '') {
            throw OrderInvariantViolation::invalidAggregatorReference();
        }

        if ($cart->lines() === []) {
            throw OrderInvariantViolation::emptyCart();
        }

        return new self(
            id: null,
            source: OrderSource::Aggregator,
            checkoutId: null,
            aggregatorReference: $aggregatorReference,
            status: OrderStatus::New,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
        );
    }

    public static function restore(
        OrderId $id,
        OrderSource $source,
        ?string $checkoutId,
        ?OrderAggregatorReference $aggregatorReference,
        OrderStatus $status,
        OrderCartSnapshot $cart,
        OrderClientSnapshot $client,
        OrderDeliverySnapshot $delivery,
        OrderPaymentSnapshot $payment,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            source: $source,
            checkoutId: $checkoutId,
            aggregatorReference: $aggregatorReference,
            status: $status,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
        );
    }

    public function id(): OrderId
    {
        if ($this->id === null) {
            throw new \LogicException('Заказ ещё не сохранён.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function assignId(OrderId $id): void
    {
        $this->id = $id;
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

    public function status(): OrderStatus
    {
        return $this->status;
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

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
