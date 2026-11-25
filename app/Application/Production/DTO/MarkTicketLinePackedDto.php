<?php

namespace App\Application\Production\DTO;

final readonly class MarkTicketLinePackedDto
{
    public function __construct(
        public int $orderId,
        public int $lineId,
        public int $employeeId,
    ) {}
}
