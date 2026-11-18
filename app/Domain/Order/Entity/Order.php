<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Event\OrderCancelled;
use App\Shared\Enum\DeliveryMethod;
use DateTimeImmutable;

/**
 * Агрегат заказа — снимок подтверждённого оформления + статусы жизненного цикла.
 * kitchenId / branchId — ссылки на Company, без копирования адреса.
 * paymentId — ссылка на Payment: способ, сумма и статус оплаты живут там.
 */
final class Order
{
    /** @var list<object> */
    private array $domainEvents = [];

    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
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
        private readonly DateTimeImmutable $createdAt,
        private ?int $kitchenId = null,
        private ?int $branchId = null,
        private ?int $paymentId = null,
    ) {}

    /**
     * Желаемое время в будущем → Preorder, иначе New.
     *
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     */
    public static function fromCheckoutSnapshot(
        string $clientRequestId,
        array $cart,
        array $client,
        array $delivery,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $scheduledAt = null,
    ): self {
        if ($clientRequestId === '') {
            throw new \InvalidArgumentException('Заказ нельзя создать без ссылки на чекаут.');
        }

        $lines = $cart['lines'] ?? null;
        if (! is_array($lines) || $lines === []) {
            throw new \InvalidArgumentException('Заказ нельзя создать с пустой корзиной.');
        }

        self::resolveDeliveryMethod($delivery);

        return new self(
            id: null,
            source: 'site',
            checkoutId: $clientRequestId,
            partnerCode: null,
            externalOrderId: null,
            status: $scheduledAt !== null && $scheduledAt > $createdAt
                ? OrderStatus::Preorder
                : OrderStatus::New,
            cart: $cart,
            client: $client,
            delivery: $delivery,
            createdAt: $createdAt,
            kitchenId: null,
            branchId: null,
            paymentId: null,
        );
    }

    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
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
        DateTimeImmutable $createdAt,
        ?int $kitchenId = null,
        ?int $branchId = null,
        ?int $paymentId = null,
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
            createdAt: $createdAt,
            kitchenId: $kitchenId,
            branchId: $branchId,
            paymentId: $paymentId,
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

    public function kitchenId(): ?int
    {
        return $this->kitchenId;
    }

    public function branchId(): ?int
    {
        return $this->branchId;
    }

    /**
     * Назначение / переназначение кухни (ссылки на Company).
     * Вызывается при подтверждении и при переназначении, пока тикет не Started/Completed.
     */
    public function assignKitchen(int $kitchenId, int $branchId): void
    {
        if ($kitchenId < 1 || $branchId < 1) {
            throw new \InvalidArgumentException('kitchenId и branchId должны быть положительными.');
        }

        if ($this->status !== OrderStatus::Confirmed) {
            throw new \InvalidArgumentException(
                'Кухню можно назначить только подтверждённому заказу.',
            );
        }

        $this->kitchenId = $kitchenId;
        $this->branchId = $branchId;
    }

    public function accept(): void
    {
        $this->transitionFrom(
            [OrderStatus::New, OrderStatus::Preorder],
            OrderStatus::Confirmed,
            'accept',
        );
    }

    public function startPreparing(): void
    {
        $this->transition(OrderStatus::Confirmed, OrderStatus::Preparing, 'startPreparing');
    }

    public function markReady(): void
    {
        $this->transition(OrderStatus::Preparing, OrderStatus::Ready, 'markReady');
    }

    public function deliveryMethod(): DeliveryMethod
    {
        return self::resolveDeliveryMethod($this->delivery);
    }

    public function canBeAssignedToRoute(): bool
    {
        return $this->status === OrderStatus::Ready
            && $this->deliveryMethod() === DeliveryMethod::Courier;
    }

    /** Доставка: Ready → AssignedToRoute */
    public function assignToRoute(): void
    {
        if ($this->deliveryMethod() !== DeliveryMethod::Courier) {
            throw new \InvalidArgumentException('Заказ на самовывоз нельзя класть в маршрутный лист.');
        }

        $this->transition(OrderStatus::Ready, OrderStatus::AssignedToRoute, 'assignToRoute');
    }

    /** Самовывоз: Ready → Delivered */
    public function markDeliveredPickup(): void
    {
        if ($this->deliveryMethod() !== DeliveryMethod::Pickup) {
            throw new \InvalidArgumentException('Заказ с доставкой нельзя выдать как самовывоз.');
        }

        $this->transition(OrderStatus::Ready, OrderStatus::Delivered, 'markDeliveredPickup');
    }

    /** Курьер взял лист: AssignedToRoute → InTransit */
    public function markInTransit(): void
    {
        $this->transition(OrderStatus::AssignedToRoute, OrderStatus::InTransit, 'markInTransit');
    }

    /** InTransit → Delivered */
    public function markDelivered(): void
    {
        $this->transition(OrderStatus::InTransit, OrderStatus::Delivered, 'markDelivered');
    }

    /** InTransit → WrittenOff */
    public function writeOff(): void
    {
        $this->transition(OrderStatus::InTransit, OrderStatus::WrittenOff, 'writeOff');
    }

    public function cancel(): void
    {
        $allowed = [
            OrderStatus::New,
            OrderStatus::Preorder,
            OrderStatus::Confirmed,
            OrderStatus::Preparing,
            OrderStatus::Ready,
            OrderStatus::AssignedToRoute,
        ];

        if (! in_array($this->status, $allowed, true)) {
            throw new \InvalidArgumentException(
                sprintf('Нельзя отменить заказ в статусе «%s».', $this->status->value),
            );
        }

        $this->status = OrderStatus::Cancelled;

        if ($this->id !== null) {
            $this->record(new OrderCancelled(orderId: $this->id));
        }
    }

    /**
     * @return list<object>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
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

    public function paymentId(): ?int
    {
        return $this->paymentId;
    }

    public function attachPayment(int $paymentId): void
    {
        if ($paymentId < 1) {
            throw new \InvalidArgumentException('paymentId должен быть положительным.');
        }

        if ($this->paymentId !== null && $this->paymentId !== $paymentId) {
            throw new \LogicException('К заказу уже привязан другой платёж.');
        }

        $this->paymentId = $paymentId;
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
        $this->transitionFrom([$from], $to, $action);
    }

    /**
     * @param  list<OrderStatus>  $from
     */
    private function transitionFrom(array $from, OrderStatus $to, string $action): void
    {
        if (! in_array($this->status, $from, true)) {
            $expected = implode('|', array_map(
                static fn (OrderStatus $status): string => $status->value,
                $from,
            ));

            throw new \InvalidArgumentException(
                sprintf(
                    'Действие «%s» недопустимо из статуса «%s» (ожидался «%s»).',
                    $action,
                    $this->status->value,
                    $expected,
                ),
            );
        }

        $this->status = $to;
    }

    private function record(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @param  array<string, mixed>  $delivery
     */
    private static function resolveDeliveryMethod(array $delivery): DeliveryMethod
    {
        $method = DeliveryMethod::tryFrom((string) ($delivery['method'] ?? ''));
        if ($method === null) {
            throw new \InvalidArgumentException('В заказе не указан способ получения.');
        }

        return $method;
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
