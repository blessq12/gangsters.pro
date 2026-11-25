<?php

namespace App\Application\Production\DTO;

final readonly class MarkTicketLineDoneDto
{
    public function __construct(
        public int $orderId,
        public int $lineId,
        public int $employeeId,
    ) {}
}
