<?php

namespace App\Application\Production\DTO;

final readonly class QueueTicketDto
{
    public function __construct(
        public int $orderId,
    ) {}
}
