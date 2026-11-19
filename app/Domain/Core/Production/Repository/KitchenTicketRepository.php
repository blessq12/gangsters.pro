<?php

namespace App\Domain\Core\Production\Repository;

use App\Domain\Core\Production\Entity\KitchenTicket;

interface KitchenTicketRepository
{
    public function findByOrderId(int $orderId): ?KitchenTicket;

    /** Поставить новый тикет в очередь (назначает id). */
    public function enqueue(KitchenTicket $ticket): void;

    public function save(KitchenTicket $ticket): void;

    /** Удалить тикет (после отмены — чтобы создать новый на ту же order_id). */
    public function delete(KitchenTicket $ticket): void;

    /**
     * Тикеты кухни в статусе Queued + Started.
     *
     * @return list<KitchenTicket>
     */
    public function listActiveByKitchen(int $kitchenId): array;
}
