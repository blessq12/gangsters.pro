<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Delivery\Event\RouteStopSettled;
use App\Domain\Order\Repository\OrderRepository;

/**
 * Delivery публикует исход остановки; Order переводит заказ в Delivered или WrittenOff.
 */
final class SettleOrderOnRouteStopSettled
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function handle(RouteStopSettled $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        match ($event->outcome) {
            RouteStopSettled::OUTCOME_DELIVERED => $order->markDelivered(),
            RouteStopSettled::OUTCOME_WRITTEN_OFF => $order->writeOff(),
            default => throw new \InvalidArgumentException('Неизвестный исход остановки: '.$event->outcome),
        };

        $this->orders->save($order);
    }
}
