<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Core\Delivery\Event\OrderAddedToRoute;
use App\Domain\Core\Order\Repository\OrderRepository;

/**
 * Delivery публикует добавление в маршрут; Order → assigned_to_route.
 */
final class MarkOrderAssignedToRouteOnOrderAddedToRoute
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function handle(OrderAddedToRoute $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $order->assignToRoute();
        $this->orders->save($order);
    }
}
