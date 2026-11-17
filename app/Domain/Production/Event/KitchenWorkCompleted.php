<?php

namespace App\Domain\Production\Event;

use DateTimeImmutable;

/** Кухня завершила работу по заказу. */
final readonly class KitchenWorkCompleted
{
    public function __construct(
        private int $orderId,
        private int $ticketId,
        private DateTimeImmutable $occurredAt,
    ) {}

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function ticketId(): int
    {
        return $this->ticketId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
