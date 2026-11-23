<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Event\KitchenTicketCompleted;

/**
 * Production публикует факт готовности; Order меняет только свой статус.
 */
final class MarkOrderReadyOnKitchenTicketCompleted
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function handle(KitchenTicketCompleted $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $order->markReady();
        $this->orders->save($order);
    }
}
