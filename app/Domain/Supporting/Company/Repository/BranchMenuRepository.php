<?php

namespace App\Domain\Supporting\Company\Repository;

use App\Domain\Supporting\Company\Entity\BranchMenuItem;

interface BranchMenuRepository
{
    /**
     * @return list<BranchMenuItem>
     */
    public function listByBranch(int $branchId): array;

    /**
     * @return list<BranchMenuItem>
     */
    public function listAvailableByBranch(int $branchId): array;

    /**
     * @param  list<int>  $catalogItemIds
     * @return array<int, BranchMenuItem> keyed by catalogItemId
     */
    public function findByBranchAndItemIds(int $branchId, array $catalogItemIds): array;

    public function upsert(BranchMenuItem $item): BranchMenuItem;

    /**
     * @param  list<BranchMenuItem>  $items
     */
    public function upsertMany(int $branchId, array $items): void;

    public function delete(int $branchId, int $catalogItemId): void;
}
