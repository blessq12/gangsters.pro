<?php

namespace App\Domain\Supporting\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\StockLevel;

interface StockLevelRepository
{
    public function findByBranchAndIngredient(int $branchId, int $ingredientId): ?StockLevel;

    /**
     * @return list<StockLevel>
     */
    public function listByBranch(int $branchId): array;

    /**
     * @return list<StockLevel>
     */
    public function listByBranchPage(int $branchId, int $offset, int $limit): array;

    public function countByBranch(int $branchId): int;

    public function save(StockLevel $level): void;
}
