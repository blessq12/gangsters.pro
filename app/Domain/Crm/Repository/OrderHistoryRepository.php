<?php

namespace App\Domain\Crm\Repository;

use App\Domain\Crm\Entity\OrderHistoryEntry;

interface OrderHistoryRepository
{
    public function save(OrderHistoryEntry $entry): void;

    public function findById(int $id): ?OrderHistoryEntry;

    /**
     * @return list<OrderHistoryEntry>
     */
    public function listByClientId(int $clientId): array;
}
