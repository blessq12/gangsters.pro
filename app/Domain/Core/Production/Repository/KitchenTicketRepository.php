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
     * Тикеты филиала в статусе Queued + Started.
     *
     * @return list<KitchenTicket>
     */
    public function listActiveByBranch(int $branchId): array;

    /**
     * @return list<KitchenTicket>
     */
    public function listActiveByBranchPage(int $branchId, int $offset, int $limit): array;

    public function countActiveByBranch(int $branchId): int;

    /**
     * Активные тикеты по статусу (queued/started), опционально по филиалу.
     *
     * @return array{queued: int, started: int}
     */
    public function countActiveByStatus(?int $branchId = null): array;
}
