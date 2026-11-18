<?php

namespace App\Domain\Production\Event;

final readonly class KitchenTicketCancelled
{
    public function __construct(
        public int $orderId,
        public int $ticketId,
    ) {}
}
