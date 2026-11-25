<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Core\Order\Event\OrderLinesChanged;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;

/**
 * Состав заказа изменён → пересборка линий тикета + пересчёт статуса заказа.
 */
final class ResyncKitchenTicketOnOrderLinesChanged
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly KitchenTicketRepository $tickets,
    ) {}

    public function handle(OrderLinesChanged $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $ticket = $this->tickets->findByOrderId($event->orderId);
        if ($ticket === null || $ticket->status() === KitchenTicketStatus::Cancelled) {
            return;
        }

        $lines = [];
        foreach ($order->productionLines() as $snapshot) {
            $lines[] = KitchenTicketLine::snapshot(
                productId: (int) $snapshot['product_id'],
                name: (string) $snapshot['name'],
                quantity: (int) $snapshot['quantity'],
            );
        }

        if ($lines === []) {
            return;
        }

        $ticket->resyncProductionLines($lines);
        $this->tickets->save($ticket);

        $order->syncStatusFromKitchenProgress(
            allDone: false,
            allPacked: false,
        );
        $this->orders->save($order);
    }
}
