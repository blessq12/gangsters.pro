<?php

namespace App\Domain\Core\Order\Event;

/**
 * Заказ выдан клиенту на самовывоз.
 */
final readonly class OrderPickedUp
{
    public function __construct(
        public int $orderId,
    ) {}
}
