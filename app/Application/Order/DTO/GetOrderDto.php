<?php

namespace App\Application\Order\DTO;

final readonly class GetOrderDto
{
    public function __construct(
        public int $orderId,
        public int $clientId,
    ) {}
}
