<?php

namespace App\Domain\Supporting\Menu\Repository;

use App\Domain\Supporting\Menu\Entity\BranchSet;

interface BranchSetRepository
{
    /**
     * @return list<BranchSet>
     */
    public function listByBranch(int $branchId): array;

    /**
     * @param  list<int>  $ids
     * @return list<BranchSet>
     */
    public function findAvailableByBranchAndIds(int $branchId, array $ids): array;

    public function findById(int $branchId, int $id): ?BranchSet;

    /**
     * @param  list<array{catalog_item_id: int, quantity: int}>  $lines
     * @param  list<int>  $tagIds
     */
    public function save(
        int $branchId,
        ?int $id,
        string $name,
        string $slug,
        ?string $description,
        bool $isAvailable,
        int $priceKopecks,
        string $status,
        array $lines,
        array $tagIds = [],
    ): BranchSet;

    public function delete(int $branchId, int $id): void;

    /**
     * @param  list<BranchSet>  $sets
     * @return array<int, int> oldId => newId (если переданы через snapshot)
     */
    public function replaceAllForBranch(int $branchId, array $sets): array;
}
