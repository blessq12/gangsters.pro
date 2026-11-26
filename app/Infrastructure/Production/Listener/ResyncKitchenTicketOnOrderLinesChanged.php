<?php

namespace App\Infrastructure\Production\Listener;

use App\Domain\Core\Order\Event\OrderLinesChanged;
use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Core\Production\ValueObject\KitchenTicketLine;

/**
 * Order публикует снимок линий; Production владеет пересборкой тикета.
 */
final class ResyncKitchenTicketOnOrderLinesChanged
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
    ) {}

    public function handle(OrderLinesChanged $event): void
    {
        $ticket = $this->tickets->findByOrderId($event->orderId);
        if ($ticket === null || $ticket->status() === KitchenTicketStatus::Cancelled) {
            return;
        }

        $lines = [];
        foreach ($event->productionLines as $snapshot) {
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
    }
}
