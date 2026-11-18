<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Delivery\Event\RouteSheetTaken;
use App\Domain\Order\Repository\OrderRepository;

/**
 * Delivery публикует факт «курьер взял лист»; Order переводит свои заказы в InTransit.
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
