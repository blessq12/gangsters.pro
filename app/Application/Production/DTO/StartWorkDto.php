<?php

namespace App\Application\Production\DTO;

final readonly class StartWorkDto
{
    public function __construct(
        public int $orderId,
    ) {}
}
