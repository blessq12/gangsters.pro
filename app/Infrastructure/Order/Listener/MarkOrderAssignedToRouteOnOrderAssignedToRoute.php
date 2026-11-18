<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Delivery\Event\OrderAssignedToRoute;
use App\Domain\Order\Repository\OrderRepository;

/**
 * Delivery публикует факт назначения на маршрут; Order меняет только свой статус.
 */
final class MarkOrderAssignedToRouteOnOrderAssignedToRoute
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function handle(OrderAssignedToRoute $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $order->assignToRoute();
        $this->orders->save($order);
    }
}
