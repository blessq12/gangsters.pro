<?php

namespace App\Domain\Checkout\Entity;

use App\Domain\Checkout\Enum\CheckoutStatus;
use App\Domain\Checkout\Event\CheckoutConfirmed;
use App\Domain\Checkout\Exception\CheckoutAlreadyConfirmedException;
use App\Domain\Checkout\Exception\CheckoutNotReadyForConfirmationException;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Checkout\ValueObject\CartSnapshot;
use App\Domain\Checkout\ValueObject\CheckoutId;
use App\Domain\Checkout\ValueObject\ClientSnapshot;
use App\Domain\Checkout\ValueObject\DeliverySnapshot;
use App\Domain\Checkout\ValueObject\PaymentSnapshot;
use DateTimeImmutable;

/**
 * Агрегат намерения оформления заказа.
 */
final class Checkout
{
    /** @var list<object> */
    private array $recordedEvents = [];

    private function __construct(
        private readonly CheckoutId $id,
        private CheckoutStatus $status,
        private CartSnapshot $cart,
        private ?ClientSnapshot $client,
        private ?DeliverySnapshot $delivery,
        private ?PaymentSnapshot $payment,
        private readonly DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $confirmedAt,
    ) {}

    public static function create(CheckoutId $id): self
    {
        return new self(
            id: $id,
            status: CheckoutStatus::Draft,
            cart: CartSnapshot::empty(),
            client: null,
            delivery: null,
            payment: null,
            createdAt: new DateTimeImmutable(),
            confirmedAt: null,
        );
    }

    public static function restore(
        CheckoutId $id,
        CheckoutStatus $status,
        CartSnapshot $cart,
        ?ClientSnapshot $client,
        ?DeliverySnapshot $delivery,
        ?PaymentSnapshot $payment,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $confirmedAt,
    ): self {
        return new self(
            id: $id,
            status: $status,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
            confirmedAt: $confirmedAt,
        );
    }

    public function id(): CheckoutId
    {
        return $this->id;
    }

    public function status(): CheckoutStatus
    {
        return $this->status;
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

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function confirmedAt(): ?DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function upsertCartLine(CartLineSnapshot $line): void
    {
        $this->assertDraft();

        $this->cart = $this->cart->upsertLine($line);
    }

    public function removeCartLine(int $productId): void
    {
        $this->assertDraft();

        $this->cart = $this->cart->removeLine($productId);
    }

    public function setCart(CartSnapshot $cart): void
    {
        $this->assertDraft();

        $this->cart = $cart;
    }

    public function setClient(ClientSnapshot $client): void
    {
        $this->assertDraft();

        $this->client = $client;
    }

    public function setDelivery(DeliverySnapshot $delivery): void
    {
        $this->assertDraft();

        $this->delivery = $delivery;
    }

    public function setPayment(PaymentSnapshot $payment): void
    {
        $this->assertDraft();

        $this->payment = $payment;
    }

    public function confirm(): CheckoutConfirmed
    {
        $this->assertDraft();
        $this->assertReadyForConfirmation();

        $this->status = CheckoutStatus::Confirmed;
        $this->confirmedAt = new DateTimeImmutable();

        $event = new CheckoutConfirmed(
            checkoutId: $this->id,
            cart: $this->cart,
            client: $this->client,
            delivery: $this->delivery,
            payment: $this->payment,
            occurredAt: $this->confirmedAt,
        );

        $this->recordedEvents[] = $event;

        return $event;
    }

    /**
     * @return list<object>
     */
    public function pullRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    private function assertDraft(): void
    {
        if ($this->status === CheckoutStatus::Confirmed) {
            throw CheckoutAlreadyConfirmedException::forId($this->id->value());
        }
    }

    private function assertReadyForConfirmation(): void
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
            throw CheckoutNotReadyForConfirmationException::missingBlocks($missingBlocks);
        }
    }
}
