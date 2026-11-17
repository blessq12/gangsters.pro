<?php

namespace App\Application\Production\DTO;

final readonly class CompleteWorkDto
{
    public function __construct(
        public int $orderId,
    ) {}
}
