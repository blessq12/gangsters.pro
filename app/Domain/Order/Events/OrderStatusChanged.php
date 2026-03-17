<?php

namespace App\Domain\Order\Events;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Shared\Events\DomainEvent;

final class OrderStatusChanged implements DomainEvent
{
    public function __construct(
        private readonly Order $order,
        private readonly OrderStatus $oldStatus,
        private readonly OrderStatus $newStatus,
    ) {
    }

    public function order(): Order
    {
        return $this->order;
    }

    public function oldStatus(): OrderStatus
    {
        return $this->oldStatus;
    }

    public function newStatus(): OrderStatus
    {
        return $this->newStatus;
    }
}

