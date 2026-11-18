<?php

namespace App\Domain\Order\Event;

final readonly class OrderCancelled
{
    public function __construct(
        public int $orderId,
    ) {}
}
