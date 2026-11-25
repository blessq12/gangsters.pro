<?php

namespace App\Domain\Core\Order\Event;

/**
 * Состав позиций заказа изменён администратором — нужен синк кухонного тикета.
 */
final readonly class OrderLinesChanged
{
    public function __construct(
        public int $orderId,
    ) {}
}
