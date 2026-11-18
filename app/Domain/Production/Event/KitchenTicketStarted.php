<?php

namespace App\Domain\Production\Event;

use DateTimeImmutable;

final readonly class KitchenTicketStarted
{
    public function __construct(
        public int $orderId,
        public int $ticketId,
        public DateTimeImmutable $startedAt,
    ) {}
}
