<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockWriteOff;

interface StockWriteOffRepository
{
    public function findById(int $id): ?StockWriteOff;

    /**
     * @return list<StockWriteOff>
     */
    public function listByKitchenTicketId(int $kitchenTicketId): array;

    public function save(StockWriteOff $writeOff): void;
}
