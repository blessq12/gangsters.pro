<?php

namespace App\Domain\Order\Events;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Shared\Events\DomainEvent;

final class OrderItemAdded implements DomainEvent
{
    public function __construct(
        private readonly Order $order,
        private readonly OrderItem $item,
    ) {
    }

    public function order(): Order
    {
        return $this->order;
    }

    public function item(): OrderItem
    {
        return $this->item;
    }
}

