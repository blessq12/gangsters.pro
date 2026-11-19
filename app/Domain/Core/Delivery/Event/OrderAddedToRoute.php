<?php

namespace App\Domain\Core\Delivery\Event;

/**
 * Заказ добавлен в маршрутный лист.
 */
final readonly class OrderAddedToRoute
{
    public function __construct(
        public int $orderId,
        public int $routeSheetId,
    ) {}
}
