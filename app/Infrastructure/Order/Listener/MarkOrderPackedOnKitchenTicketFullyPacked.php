<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Event\KitchenTicketLinePacked;

/**
 * Все позиции тикета собраны → заказ packed.
 */
final class MarkOrderPackedOnKitchenTicketFullyPacked
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function handle(KitchenTicketLinePacked $event): void
    {
        if (! $event->allLinesPacked) {
            return;
        }

        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $order->markPacked();
        $this->orders->save($order);
    }
}
