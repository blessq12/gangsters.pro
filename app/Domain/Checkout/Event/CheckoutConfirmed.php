<?php

namespace App\Domain\Checkout\Event;

use App\Domain\Checkout\Enum\CheckoutStatus;
use App\Domain\Checkout\ValueObject\CartSnapshot;
use App\Domain\Checkout\ValueObject\CheckoutId;
use App\Domain\Checkout\ValueObject\ClientSnapshot;
use App\Domain\Checkout\ValueObject\DeliverySnapshot;
use App\Domain\Checkout\ValueObject\PaymentSnapshot;
use DateTimeImmutable;

final readonly class CheckoutConfirmed
{
    public function __construct(
        private CheckoutId $checkoutId,
        private CartSnapshot $cart,
        private ClientSnapshot $client,
        private DeliverySnapshot $delivery,
        private PaymentSnapshot $payment,
        private DateTimeImmutable $occurredAt,
    ) {}

    public function checkoutId(): CheckoutId
    {
        return $this->checkoutId;
    }

    public function cart(): CartSnapshot
    {
        return $this->cart;
    }

    public function client(): ClientSnapshot
    {
        return $this->client;
    }

    public function delivery(): DeliverySnapshot
    {
        return $this->delivery;
    }

    public function payment(): PaymentSnapshot
    {
        return $this->payment;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function status(): CheckoutStatus
    {
        return CheckoutStatus::Confirmed;
    }
}
