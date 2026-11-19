<?php

namespace App\Infrastructure\Production\Listener;

use App\Domain\Core\Order\Event\OrderConfirmed;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Production\Entity\KitchenTicket;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;

/**
 * Order подтверждён → Production создаёт KitchenTicket со снимком позиций.
 */
final class CreateKitchenTicketOnOrderConfirmed
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly KitchenTicketRepository $tickets,
    ) {}

    public function handle(OrderConfirmed $event): void
    {
        $existing = $this->tickets->findByOrderId($event->orderId);
        if ($existing !== null) {
            // Активный или отменённый тикет на order_id уже есть (unique) — не дублируем.
            return;
        }

        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            return;
        }

        $lines = [];
        foreach ($order->productionLines() as $line) {
            $lines[] = new KitchenTicketLine(
                productId: $line->productId(),
                name: $line->name(),
                quantity: $line->quantity(),
            );
        }

        $ticket = KitchenTicket::queue(
            orderId: $event->orderId,
            kitchenId: $event->kitchenId,
            lines: $lines,
        );
        $this->tickets->enqueue($ticket);
    }
}
