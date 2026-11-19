<?php

namespace App\Domain\Core\Production\Event;

final readonly class KitchenTicketCancelled
{
    public function __construct(
        public int $orderId,
        public int $ticketId,
    ) {}
}
