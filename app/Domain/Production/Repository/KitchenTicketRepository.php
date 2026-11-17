<?php

namespace App\Domain\Production\Repository;

use App\Domain\Production\Entity\KitchenTicket;

interface KitchenTicketRepository
{
    public function findByOrderId(int $orderId): ?KitchenTicket;

    /** Поставить новый тикет в очередь (назначает id). */
    public function enqueue(KitchenTicket $ticket): void;

    public function save(KitchenTicket $ticket): void;
}
