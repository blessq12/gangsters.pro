<?php

namespace App\Domain\Order\OrderDraft\Entity;

use App\Domain\Order\OrderDraft\Exception\OrderDraftNotReadyException;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\CartSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\ClientSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\DeliverySnapshot;
use App\Domain\Order\OrderDraft\ValueObject\PaymentSnapshot;

/**
 * In-memory черновик заказа для preview и place (без персистентности).
 */
final class OrderDraft
{
    private function __construct(
        private CartSnapshot $cart,
        private ?ClientSnapshot $client,
        private ?DeliverySnapshot $delivery,
        private ?PaymentSnapshot $payment,
    ) {}

    public static function empty(): self
    {
        return new self(
            cart: CartSnapshot::empty(),
            client: null,
            delivery: null,
            payment: null,
        );
    }

    public static function restore(
        CartSnapshot $cart,
        ?ClientSnapshot $client,
        ?DeliverySnapshot $delivery,
        ?PaymentSnapshot $payment,
    ): self {
        return new self(
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
        );
    }

    public function cart(): CartSnapshot
    {
        return $this->cart;
    }

    public function client(): ?ClientSnapshot
    {
        return $this->client;
    }

    public function delivery(): ?DeliverySnapshot
    {
        return $this->delivery;
    }

    public function payment(): ?PaymentSnapshot
    {
        return $this->payment;
    }

    public function setCart(CartSnapshot $cart): void
    {
        $this->cart = $cart;
    }

    public function setClient(ClientSnapshot $client): void
    {
        $this->client = $client;
    }

    public function setDelivery(DeliverySnapshot $delivery): void
    {
        $this->delivery = $delivery;
    }

    public function setPayment(PaymentSnapshot $payment): void
    {
        $this->payment = $payment;
    }

    public function assertReadyForPlace(): void
    {
        $missingBlocks = [];

        if (! $this->cart->hasItems()) {
            $missingBlocks[] = 'cart';
        }

        if ($this->client === null) {
            $missingBlocks[] = 'client';
        }

        if ($this->delivery === null) {
            $missingBlocks[] = 'delivery';
        }

        if ($this->payment === null) {
            $missingBlocks[] = 'payment';
        }

        if ($missingBlocks !== []) {
            throw OrderDraftNotReadyException::missingBlocks($missingBlocks);
        }
    }
}
