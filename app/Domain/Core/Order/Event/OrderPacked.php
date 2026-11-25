<?php

namespace App\Domain\Core\Order\Event;

/**
 * Заказ собран администратором (все позиции тикета packed).
 */
final readonly class OrderPacked
{
    public function __construct(
        public int $orderId,
    ) {}
}
