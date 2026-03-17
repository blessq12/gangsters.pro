<?php

namespace App\Domain\Order\Events;

use App\Domain\Order\Entities\Order;

final class OrderCreated
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

