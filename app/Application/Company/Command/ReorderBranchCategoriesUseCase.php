<?php

namespace App\Application\Company\Command;

use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Порядок категорий филиала: sort_order = позиция id в переданном списке.
 */
final class ReorderBranchCategoriesUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchCategoryRepository $categories,
    ) {}

    /**
     * @param  list<int>  $ids
     * @return array{ok: true}
     */
    public function execute(int $branchId, array $ids): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $ordered = [];
        foreach (array_values($ids) as $index => $id) {
            $ordered[] = ['id' => $id, 'sort_order' => $index];
        }

        $this->categories->reorder($branchId, $ordered);

        return ['ok' => true];
    }
}
