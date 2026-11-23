<?php

namespace App\Domain\Supporting\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\StockWriteOff;

interface StockWriteOffRepository
{
    public function findById(int $id): ?StockWriteOff;

    /**
     * @return list<StockWriteOff>
     */
    public function listByBranch(int $branchId): array;

    /**
     * @return list<StockWriteOff>
     */
    public function listByBranchPage(int $branchId, int $offset, int $limit): array;

    public function countByBranch(int $branchId): int;

    /**
     * @return list<StockWriteOff>
     */
    public function listByKitchenTicketId(int $kitchenTicketId): array;

    /**
     * @return list<StockWriteOff>
     */
    public function listByBranchAndIngredient(int $branchId, int $ingredientId): array;

    public function save(StockWriteOff $writeOff): void;
}
