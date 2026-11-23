<?php

namespace App\Domain\Core\Order\Event;

final readonly class OrderCancelled
{
    public function __construct(
        public int $orderId,
    ) {}
}
