<?php

namespace App\Domain\Order\Event;

use App\Domain\Order\Entity\Order;
use DateTimeImmutable;

final readonly class OrderCreated
{
    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $payment
     */
    public function __construct(
        private int $orderId,
        private string $source,
        private ?string $checkoutId,
        private ?string $partnerCode,
        private ?string $externalOrderId,
        private array $cart,
        private array $client,
        private array $delivery,
        private array $payment,
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
            partnerCode: $order->partnerCode(),
            externalOrderId: $order->externalOrderId(),
            cart: $order->cart(),
            client: $order->client(),
            delivery: $order->delivery(),
            payment: $order->payment(),
            occurredAt: $order->createdAt(),
        );
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function checkoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function partnerCode(): ?string
    {
        return $this->partnerCode;
    }

    public function externalOrderId(): ?string
    {
        return $this->externalOrderId;
    }

    /**
     * @return array<string, mixed>
     */
    public function cart(): array
    {
        return $this->cart;
    }

    /**
     * @return array<string, mixed>
     */
    public function client(): array
    {
        return $this->client;
    }

    /**
     * @return array<string, mixed>
     */
    public function delivery(): array
    {
        return $this->delivery;
    }

    /**
     * @return array<string, mixed>
     */
    public function payment(): array
    {
        return $this->payment;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
