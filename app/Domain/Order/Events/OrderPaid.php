<?php

namespace App\Domain\Order\Events;

use App\Domain\Order\Entities\Order;
use App\Shared\Events\DomainEvent;

final class OrderPaid implements DomainEvent
{
    public function __construct(
        private readonly Order $order,
    ) {
    }

    public function order(): Order
    {
        return $this->order;
    }
}

