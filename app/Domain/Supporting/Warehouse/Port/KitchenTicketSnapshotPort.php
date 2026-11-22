<?php

namespace App\Domain\Supporting\Warehouse\Port;

/**
 * Снимок кухонного тикета для списания. Warehouse Domain не импортирует Production.
 *
 * @phpstan-type TicketLineSnapshot array{entry_id: int, quantity: int}
 * @phpstan-type TicketSnapshot array{
 *     ticket_id: int,
 *     order_id: int,
 *     branch_id: int,
 *     lines: list<TicketLineSnapshot>
 * }
 */
interface KitchenTicketSnapshotPort
{
    /**
     * @return TicketSnapshot|null
     */
    public function findCompletedTicket(int $ticketId, int $orderId): ?array;
}
