<?php

namespace App\Application\Menu\Command;

use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;

/**
 * Порядок категорий филиала: sort_order = позиция id в переданном списке.
 */
final class ReorderBranchCategoriesUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchCategoryRepository $categories,
    ) {}

    /**
     * @param  list<int>  $ids
     * @return array{ok: true}
     */
    public function execute(int $branchId, array $ids): array
    {
        $this->branches->assertBranchExists($branchId);

        $ordered = [];
        foreach (array_values($ids) as $index => $id) {
            $ordered[] = ['id' => $id, 'sort_order' => $index];
        }

        $this->categories->reorder($branchId, $ordered);

        return ['ok' => true];
    }
}
