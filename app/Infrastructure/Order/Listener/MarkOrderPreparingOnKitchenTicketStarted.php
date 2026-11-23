<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Event\KitchenTicketStarted;

/**
 * Production стартовала тикет → Order в preparing.
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
