<?php

namespace App\Domain\Production\Event;

use DateTimeImmutable;

/** Кухня начала работу по заказу. */
final readonly class KitchenWorkStarted
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
