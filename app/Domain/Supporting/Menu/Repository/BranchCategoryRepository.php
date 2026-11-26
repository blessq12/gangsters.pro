<?php

namespace App\Domain\Supporting\Menu\Repository;

use App\Domain\Supporting\Menu\Entity\BranchCategory;
use App\Domain\Supporting\Menu\Entity\BranchCategoryItem;

interface BranchCategoryRepository
{
    /**
     * @return list<BranchCategory>
     */
    public function listByBranch(int $branchId): array;

    /**
     * @return list<BranchCategory>
     */
    public function listActiveOrderedByBranch(int $branchId): array;

    public function findById(int $branchId, int $id): ?BranchCategory;

    /**
     * @return list<BranchCategoryItem>
     */
    public function findItemsByCategoryId(int $categoryId): array;

    /**
     * @param  list<array{entry_kind: string, entry_id: int, sort_order: int}>  $items
     */
    public function save(
        int $branchId,
        ?int $id,
        string $name,
        string $slug,
        int $sortOrder,
        bool $isActive,
        bool $isAccompanying,
        bool $isComplement = false,
        bool $isGift = false,
        ?array $items = null,
    ): BranchCategory;

    /**
     * @param  list<array{id: int, sort_order: int}>  $ordered
     */
    public function reorder(int $branchId, array $ordered): void;

    /**
     * @param  list<array{entry_kind: string, entry_id: int, sort_order: int}>  $items
     */
    public function replaceItems(int $branchId, int $categoryId, array $items): void;

    public function delete(int $branchId, int $id): void;

    /**
     * @param  list<BranchCategory>  $categories
     * @param  array<int, list<BranchCategoryItem>>  $itemsByCategoryId
     */
    public function replaceAllForBranch(int $branchId, array $categories, array $itemsByCategoryId): void;
}
