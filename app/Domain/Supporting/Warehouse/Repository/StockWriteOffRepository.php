<?php

namespace App\Domain\Supporting\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\StockWriteOff;

interface StockWriteOffRepository
{
    public function findById(int $id): ?StockWriteOff;

    /**
     * @return list<StockWriteOff>
     */
    public function listAll(): array;

    /**
     * @return list<StockWriteOff>
     */
    public function listPage(int $offset, int $limit): array;

    public function countAll(): int;

    /**
     * @return list<StockWriteOff>
     */
    public function listByKitchenTicketId(int $kitchenTicketId): array;

    public function save(StockWriteOff $writeOff): void;
}
