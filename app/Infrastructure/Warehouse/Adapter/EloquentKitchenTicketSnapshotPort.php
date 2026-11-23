<?php

namespace App\Infrastructure\Warehouse\Adapter;

use App\Domain\Core\Production\Enum\KitchenTicketStatus;
use App\Domain\Core\Production\Repository\KitchenTicketRepository;
use App\Domain\Supporting\Warehouse\Port\KitchenTicketSnapshotPort;

/**
 * ACL: Warehouse читает снимок тикета через Production-репозиторий, не через Domain Catalog/Company.
 */
final class EloquentKitchenTicketSnapshotPort implements KitchenTicketSnapshotPort
{
    public function __construct(
        private readonly KitchenTicketRepository $tickets,
    ) {}

    public function findCompletedTicket(int $ticketId, int $orderId): ?array
    {
        $ticket = $this->tickets->findByOrderId($orderId);
        if ($ticket === null || $ticket->id() !== $ticketId) {
            return null;
        }

        if ($ticket->status() !== KitchenTicketStatus::Completed) {
            return null;
        }

        $lines = [];
        foreach ($ticket->lines() as $line) {
            $lines[] = [
                'entry_id' => $line->productId(),
                'quantity' => $line->quantity(),
            ];
        }

        return [
            'ticket_id' => $ticket->id(),
            'order_id' => $ticket->orderId(),
            'branch_id' => $ticket->branchId(),
            'lines' => $lines,
        ];
    }
}
