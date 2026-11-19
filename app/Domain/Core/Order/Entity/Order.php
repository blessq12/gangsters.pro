<?php

namespace App\Domain\Core\Order\Entity;

use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Enum\OrderStatus;
use App\Domain\Core\Order\Event\KitchenReassigned;
use App\Domain\Core\Order\Event\OrderCancelled;
use App\Domain\Core\Order\Event\OrderConfirmed;
use App\Domain\Core\Order\Event\OrderPickedUp;
use App\Domain\Core\Order\Event\OrderPlaced;
use App\Domain\Core\Order\Policy\OrderFormation;
use App\Domain\Core\Order\Policy\PromotionPolicy;
use App\Domain\Core\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Shared\Enum\DeliveryMethod;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Агрегат заказа: позиции + снимки клиента/доставки + жизненный цикл.
 * kitchenId / branchId — ссылки на Company; paymentId — ссылка на Payment.
 */
final class Order
{
    /** @var list<object> */
    private array $domainEvents = [];

    /**
     * @param  list<OrderLine>  $lines
     */
    private function __construct(
        private ?int $id,
        private readonly string $source,
        private readonly ?string $checkoutId,
        private readonly ?string $partnerCode,
        private readonly ?string $externalOrderId,
        private OrderStatus $status,
        private array $lines,
        private OrderClientSnapshot $client,
        private OrderDeliveryInfo $delivery,
        private readonly DateTimeImmutable $createdAt,
        private ?int $kitchenId = null,
        private ?int $branchId = null,
        private ?int $paymentId = null,
        private ?string $paymentMethod = null,
        private ?int $changeFromRubles = null,
    ) {
        self::assertLines($this->lines);
    }

    /**
     * Создаёт заказ из базовых позиций и применяет коммерческие политики.
     *
     * @param  list<OrderLine>  $baseLines  только regular|set
     * @param  list<ProductPromotionRole>  $roles
     */
    public static function place(
        string $checkoutId,
        OrderClientSnapshot $client,
        OrderDeliveryInfo $delivery,
        array $baseLines,
        PromotionPolicy $policy,
        array $roles,
        string $channel,
        DateTimeImmutable $createdAt,
        string $timezone = 'UTC',
        ?DateTimeImmutable $scheduledAt = null,
        string $source = 'site',
        ?string $partnerCode = null,
        ?string $externalOrderId = null,
    ): self {
        if ($checkoutId === '') {
            throw new \InvalidArgumentException('Заказ нельзя создать без ссылки на чекаут.');
        }

        $formed = OrderFormation::apply(
            baseLines: $baseLines,
            delivery: $delivery,
            policy: $policy,
            roles: $roles,
            channel: $channel,
            at: $createdAt,
            timezone: $timezone,
        );

        return new self(
            id: null,
            source: $source,
            checkoutId: $checkoutId,
            partnerCode: $partnerCode,
            externalOrderId: $externalOrderId,
            status: $scheduledAt !== null && $scheduledAt > $createdAt
                ? OrderStatus::Preorder
                : OrderStatus::New,
            lines: $formed['lines'],
            client: $client,
            delivery: $formed['delivery'],
            createdAt: $createdAt,
        );
    }

    /**
     * Создание заказа из уже сформированных позиций (снимок чекаута).
     * Политики и инварианты чекаута уже применены снаружи.
     *
     * @param  list<OrderLine>  $lines
     */
    public static function compose(
        string $checkoutId,
        OrderClientSnapshot $client,
        OrderDeliveryInfo $delivery,
        array $lines,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $scheduledAt = null,
        string $source = 'site',
        ?string $partnerCode = null,
        ?string $externalOrderId = null,
    ): self {
        if ($checkoutId === '') {
            throw new \InvalidArgumentException('Заказ нельзя создать без ссылки на чекаут.');
        }

        return new self(
            id: null,
            source: $source,
            checkoutId: $checkoutId,
            partnerCode: $partnerCode,
            externalOrderId: $externalOrderId,
            status: $scheduledAt !== null && $scheduledAt > $createdAt
                ? OrderStatus::Preorder
                : OrderStatus::New,
            lines: array_values($lines),
            client: $client,
            delivery: $delivery,
            createdAt: $createdAt,
        );
    }

    /**
     * @param  list<OrderLine>  $lines
     */
    public static function restore(
        int $id,
        string $source,
        ?string $checkoutId,
        ?string $partnerCode,
        ?string $externalOrderId,
        OrderStatus|string $status,
        array $lines,
        OrderClientSnapshot $client,
        OrderDeliveryInfo $delivery,
        DateTimeImmutable $createdAt,
        ?int $kitchenId = null,
        ?int $branchId = null,
        ?int $paymentId = null,
        ?string $paymentMethod = null,
        ?int $changeFromRubles = null,
    ): self {
        return new self(
            id: $id,
            source: $source,
            checkoutId: $checkoutId,
            partnerCode: $partnerCode,
            externalOrderId: $externalOrderId,
            status: self::normalizeStatus($status),
            lines: $lines,
            client: $client,
            delivery: $delivery,
            createdAt: $createdAt,
            kitchenId: $kitchenId,
            branchId: $branchId,
            paymentId: $paymentId,
            paymentMethod: $paymentMethod,
            changeFromRubles: $changeFromRubles,
        );
    }

    /**
     * Пересчёт выгод по актуальным политикам. Только до старта готовки.
     *
     * @param  list<OrderLine>  $baseLines  только regular|set; null = взять текущие billable
     * @param  list<ProductPromotionRole>  $roles
     */
    public function recalculate(
        PromotionPolicy $policy,
        array $roles,
        string $channel,
        DateTimeInterface $at,
        string $timezone,
        ?array $baseLines = null,
        ?OrderDeliveryInfo $delivery = null,
    ): void {
        if (! in_array($this->status, [OrderStatus::New, OrderStatus::Preorder, OrderStatus::Confirmed], true)) {
            throw new \InvalidArgumentException('Пересчёт доступен только до начала готовки.');
        }

        $base = $baseLines ?? array_values(array_filter(
            $this->lines,
            static fn (OrderLine $line): bool => $line->isBillable(),
        ));

        $formed = OrderFormation::apply(
            baseLines: $base,
            delivery: $delivery ?? $this->delivery,
            policy: $policy,
            roles: $roles,
            channel: $channel,
            at: $at,
            timezone: $timezone,
        );

        $this->lines = $formed['lines'];
        $this->delivery = $formed['delivery'];
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

    /**
     * Фиксирует факт оформления после получения id.
     * Условия оплаты запоминаются до OrderConfirmed (Payment создаётся по подтверждению).
     */
    public function markPlaced(string $paymentMethod, ?int $changeFromRubles = null): void
    {
        if ($this->id === null) {
            throw new \LogicException('Нельзя зафиксировать оформление без id заказа.');
        }

        if (trim($paymentMethod) === '') {
            throw new \InvalidArgumentException('Способ оплаты обязателен при оформлении.');
        }

        $this->paymentMethod = $paymentMethod;
        $this->changeFromRubles = $changeFromRubles;

        $this->record(new OrderPlaced(
            orderId: $this->id,
            amountKopecks: $this->grandTotalKopecks(),
            paymentMethod: $paymentMethod,
            changeFromRubles: $changeFromRubles,
        ));
    }

    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function changeFromRubles(): ?int
    {
        return $this->changeFromRubles;
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

    /**
     * @return list<OrderLine>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function client(): OrderClientSnapshot
    {
        return $this->client;
    }

    public function delivery(): OrderDeliveryInfo
    {
        return $this->delivery;
    }

    public function kitchenId(): ?int
    {
        return $this->kitchenId;
    }

    public function branchId(): ?int
    {
        return $this->branchId;
    }

    public function paymentId(): ?int
    {
        return $this->paymentId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function clientId(): ?int
    {
        return $this->client->clientId();
    }

    public function deliveryMethod(): DeliveryMethod
    {
        return $this->delivery->type();
    }

    /** Сумма billable-позиций без доставки. */
    public function subtotalKopecks(): int
    {
        $total = 0;
        foreach ($this->lines as $line) {
            if ($line->isBillable()) {
                $total += $line->lineTotalKopecks();
            }
        }

        return $total;
    }

    /** Итого к оплате: billable + fee доставки. */
    public function grandTotalKopecks(): int
    {
        return $this->subtotalKopecks() + $this->delivery->feeKopecks();
    }

    /**
     * Позиции для кухни: всё, что нужно приготовить (включая gift/complement/set).
     *
     * @return list<OrderLine>
     */
    public function productionLines(): array
    {
        return array_values(array_filter(
            $this->lines,
            static fn (OrderLine $line): bool => in_array(
                $line->lineType(),
                [OrderLineType::Regular, OrderLineType::Set, OrderLineType::Gift, OrderLineType::Complement],
                true,
            ),
        ));
    }

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

    /**
     * Переназначение кухни после первичного назначения.
     * Production отменяет старый тикет и создаёт новый по KitchenReassigned.
     */
    public function reassignKitchen(int $kitchenId, int $branchId): void
    {
        if ($kitchenId < 1 || $branchId < 1) {
            throw new \InvalidArgumentException('kitchenId и branchId должны быть положительными.');
        }

        if ($this->id === null) {
            throw new \LogicException('Нельзя переназначить кухню без id заказа.');
        }

        if ($this->kitchenId === null) {
            throw new \InvalidArgumentException('Кухня ещё не назначена — используй assignKitchen.');
        }

        if (! in_array($this->status, [OrderStatus::Confirmed, OrderStatus::Preparing], true)) {
            throw new \InvalidArgumentException(
                'Переназначение кухни доступно только до готовности заказа.',
            );
        }

        if ($kitchenId === $this->kitchenId) {
            throw new \InvalidArgumentException('Указана та же кухня.');
        }

        $previousKitchenId = $this->kitchenId;
        $this->kitchenId = $kitchenId;
        $this->branchId = $branchId;

        $this->record(new KitchenReassigned(
            orderId: $this->id,
            previousKitchenId: $previousKitchenId,
            kitchenId: $kitchenId,
        ));
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

    public function accept(): void
    {
        $this->transitionFrom(
            [OrderStatus::New, OrderStatus::Preorder],
            OrderStatus::Confirmed,
            'accept',
        );
    }

    /**
     * Публикует OrderConfirmed после accept + assignKitchen.
     * Тикет и платёж создаются слушателями этого события.
     */
    public function confirm(): void
    {
        if ($this->id === null) {
            throw new \LogicException('Нельзя подтвердить заказ без id.');
        }

        if ($this->status !== OrderStatus::Confirmed) {
            throw new \InvalidArgumentException(
                sprintf('Подтверждение доступно только в статусе «confirmed», сейчас «%s».', $this->status->value),
            );
        }

        if ($this->kitchenId === null) {
            throw new \InvalidArgumentException('Перед подтверждением нужно назначить кухню.');
        }

        if ($this->paymentMethod === null || trim($this->paymentMethod) === '') {
            throw new \LogicException('Нет условий оплаты для подтверждения заказа.');
        }

        $this->record(new OrderConfirmed(
            orderId: $this->id,
            kitchenId: $this->kitchenId,
            amountKopecks: $this->grandTotalKopecks(),
            paymentMethod: $this->paymentMethod,
            changeFromRubles: $this->changeFromRubles,
        ));
    }

    public function startPreparing(): void
    {
        $this->transition(OrderStatus::Confirmed, OrderStatus::Preparing, 'startPreparing');
    }

    public function markReady(): void
    {
        $this->transitionFrom(
            [OrderStatus::Confirmed, OrderStatus::Preparing],
            OrderStatus::Ready,
            'markReady',
        );
    }

    public function canBeAssignedToRoute(): bool
    {
        return $this->status === OrderStatus::Ready && $this->delivery->isCourier();
    }

    public function assignToRoute(): void
    {
        if (! $this->delivery->isCourier()) {
            throw new \InvalidArgumentException('Заказ на самовывоз нельзя класть в маршрутный лист.');
        }

        $this->transition(OrderStatus::Ready, OrderStatus::AssignedToRoute, 'assignToRoute');
    }

    public function markDeliveredPickup(): void
    {
        if (! $this->delivery->isPickup()) {
            throw new \InvalidArgumentException('Заказ с доставкой нельзя выдать как самовывоз.');
        }

        $this->transition(OrderStatus::Ready, OrderStatus::Delivered, 'markDeliveredPickup');

        if ($this->id !== null) {
            $this->record(new OrderPickedUp(orderId: $this->id));
        }
    }

    public function markInTransit(): void
    {
        $this->transition(OrderStatus::AssignedToRoute, OrderStatus::InTransit, 'markInTransit');
    }

    public function markDelivered(): void
    {
        $this->transitionFrom(
            [OrderStatus::AssignedToRoute, OrderStatus::InTransit],
            OrderStatus::Delivered,
            'markDelivered',
        );
    }

    public function writeOff(): void
    {
        $this->transitionFrom(
            [OrderStatus::AssignedToRoute, OrderStatus::InTransit],
            OrderStatus::WrittenOff,
            'writeOff',
        );
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
     * @param  list<OrderLine>  $lines
     */
    private static function assertLines(array $lines): void
    {
        if ($lines === []) {
            throw new \InvalidArgumentException('Заказ нельзя создать с пустой корзиной.');
        }

        foreach ($lines as $line) {
            if (! $line instanceof OrderLine) {
                throw new \InvalidArgumentException('Ожидался OrderLine.');
            }
        }
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
