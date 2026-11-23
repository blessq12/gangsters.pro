<?php

namespace App\Application\Order\DTO;

final readonly class AcceptOrderDto
{
    public function __construct(
        public int $orderId,
    ) {}
}
