<?php

namespace App\Domain\Delivery\Event;

use DateTimeImmutable;

/** Остановка маршрута закрыта: заказ вручен или не вручен; Order обновляет статус. */
final readonly class StopDelivered
{
    public const OUTCOME_DELIVERED = 'delivered';

    public const OUTCOME_FAILED = 'failed';

    public function __construct(
        private int $routeSheetId,
        private int $orderId,
        private string $outcome,
        private string $reason,
        private DateTimeImmutable $occurredAt,
    ) {
        if (! in_array($outcome, [self::OUTCOME_DELIVERED, self::OUTCOME_FAILED], true)) {
            throw new \InvalidArgumentException('Недопустимый исход остановки.');
        }
    }

    public function routeSheetId(): int
    {
        return $this->routeSheetId;
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    /** delivered|failed */
    public function outcome(): string
    {
        return $this->outcome;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
