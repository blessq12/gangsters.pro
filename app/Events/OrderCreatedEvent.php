<?php

namespace App\Events;

use App\Domain\Order\Entities\Order;

final class OrderCreatedEvent
{
    public function __construct(
        public readonly Order $order,
    ) {
    }
}

