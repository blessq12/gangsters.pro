<?php

namespace App\Application\Operations\Order\DTO;

final readonly class ChangeOrderStatusDTO
{
    public function __construct(
        public string $orderId,
        public string $status,
    ) {
    }
}
