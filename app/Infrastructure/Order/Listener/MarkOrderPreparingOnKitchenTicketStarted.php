<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Production\Event\KitchenTicketStarted;

/**
 * Production публикует факт старта; Order меняет только свой статус.
 */
final class MarkOrderPreparingOnKitchenTicketStarted
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function handle(KitchenTicketStarted $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $order->startPreparing();
        $this->orders->save($order);
    }
}
