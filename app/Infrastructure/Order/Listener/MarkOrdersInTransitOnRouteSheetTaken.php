<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Core\Delivery\Event\RouteSheetTaken;
use App\Domain\Core\Order\Repository\OrderRepository;

/**
 * Курьер взял лист → все заказы листа в in_transit.
 */
final class MarkOrdersInTransitOnRouteSheetTaken
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function handle(RouteSheetTaken $event): void
    {
        foreach ($event->orderIds as $orderId) {
            $order = $this->orders->findById($orderId);
            if ($order === null) {
                continue;
            }

            $order->markInTransit();
            $this->orders->save($order);
        }
    }
}
