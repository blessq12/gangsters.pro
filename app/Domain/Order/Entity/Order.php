<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\Enum\OrderStatus;
use DateTimeImmutable;

/**
 * Агрегат заказа — снимок подтверждённого оформления + статусы жизненного цикла.
 */
final class Order
{
    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $payment
     */
    private function __construct(
        private ?int $id,
        private readonly string $source,
        private readonly ?string $checkoutId,
        private readonly ?string $partnerCode,
        private readonly ?string $externalOrderId,
        private OrderStatus $status,
        private readonly array $cart,
        private readonly array $client,
        private readonly array $delivery,
        private readonly array $payment,
        private readonly DateTimeImmutable $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $payment
     */
    public static function fromCheckoutSnapshot(
        string $clientRequestId,
        array $cart,
        array $client,
        array $delivery,
        array $payment,
        DateTimeImmutable $createdAt,
    ): self {
        if ($clientRequestId === '') {
            throw new \InvalidArgumentException('Заказ нельзя создать без ссылки на чекаут.');
        }

        $lines = $cart['lines'] ?? null;
        if (! is_array($lines) || $lines === []) {
            throw new \InvalidArgumentException('Заказ нельзя создать с пустой корзиной.');
        }

        return new self(
            id: null,
            source: 'site',
            checkoutId: $clientRequestId,
            partnerCode: null,
            externalOrderId: null,
            status: OrderStatus::New,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
        );
    }

    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $payment
     */
    public static function restore(
        int $id,
        string $source,
        ?string $checkoutId,
        ?string $partnerCode,
        ?string $externalOrderId,
        OrderStatus|string $status,
        array $cart,
        array $client,
        array $delivery,
        array $payment,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            source: $source,
            checkoutId: $checkoutId,
            partnerCode: $partnerCode,
            externalOrderId: $externalOrderId,
            status: self::normalizeStatus($status),
            cart: $cart,
            client: $client,
            delivery: $delivery,
            payment: $payment,
            createdAt: $createdAt,
        );
    }

    public function id(): int
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

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        if ($id < 1) {
            throw new \InvalidArgumentException('id должен быть положительным.');
        }

        $this->id = $id;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function checkoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function clientRequestId(): ?string
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

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function accept(): void
    {
        $this->transition(OrderStatus::New, OrderStatus::Confirmed, 'accept');
    }

    public function startPreparing(): void
    {
        $this->transition(OrderStatus::Confirmed, OrderStatus::Preparing, 'startPreparing');
    }

    public function markReady(): void
    {
        $this->transition(OrderStatus::Preparing, OrderStatus::Ready, 'markReady');
    }

    public function markInTransit(): void
    {
        $this->transition(OrderStatus::Ready, OrderStatus::InTransit, 'markInTransit');
    }

    public function markDelivered(): void
    {
        $this->transition(OrderStatus::InTransit, OrderStatus::Delivered, 'markDelivered');
    }

    public function completePickup(): void
    {
        $this->transition(OrderStatus::Ready, OrderStatus::Completed, 'completePickup');
    }

    public function cancel(): void
    {
        $allowed = [
            OrderStatus::New,
            OrderStatus::Confirmed,
            OrderStatus::Preparing,
            OrderStatus::Ready,
        ];

        if (! in_array($this->status, $allowed, true)) {
            throw new \InvalidArgumentException(
                sprintf('Нельзя отменить заказ в статусе «%s».', $this->status->value),
            );
        }

        $this->status = OrderStatus::Cancelled;
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

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function clientId(): ?int
    {
        $clientId = $this->client['client_id'] ?? null;

        return $clientId !== null ? (int) $clientId : null;
    }

    public function totalRubles(): int
    {
        $totals = $this->cart['totals'] ?? null;
        if (is_array($totals) && isset($totals['grand_total_rubles'])) {
            return (int) $totals['grand_total_rubles'];
        }

        $total = 0;

        foreach ($this->cart['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $kind = is_array($line['payload'] ?? null)
                ? (string) (($line['payload']['kind'] ?? '') ?: 'user')
                : 'user';

            if (in_array($kind, ['gift', 'complement', 'delivery'], true)) {
                continue;
            }

            $total += (int) ($line['line_total_rubles'] ?? 0);
        }

        return $total;
    }

    private function transition(OrderStatus $from, OrderStatus $to, string $action): void
    {
        if ($this->status !== $from) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Действие «%s» недопустимо из статуса «%s» (ожидался «%s»).',
                    $action,
                    $this->status->value,
                    $from->value,
                ),
            );
        }

        $this->status = $to;
    }

    private static function normalizeStatus(OrderStatus|string $status): OrderStatus
    {
        if ($status instanceof OrderStatus) {
            return $status;
        }

        try {
            return OrderStatus::from($status);
        } catch (\ValueError) {
            throw new \InvalidArgumentException('Недопустимый статус заказа: '.$status);
        }
    }
}
