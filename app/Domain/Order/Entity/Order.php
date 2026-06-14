<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Exception\OrderInvariantViolation;
use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;
use DateTimeImmutable;

/**
 * Агрегат заказа — неизменяемый снимок подтверждённого чекаута.
 */
final class Order
{
    private function __construct(
        private ?OrderId $id,
        private readonly string $checkoutId,
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

        $order = new self(
            id: null,
            checkoutId: $checkoutId,
            status: OrderStatus::New,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
        );

        return $order;
    }

    public static function restore(
        OrderId $id,
        string $checkoutId,
        OrderStatus $status,
        OrderCartSnapshot $cart,
        OrderClientSnapshot $client,
        OrderDeliverySnapshot $delivery,
        OrderPaymentSnapshot $payment,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            checkoutId: $checkoutId,
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

    public function checkoutId(): string
    {
        return $this->checkoutId;
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
