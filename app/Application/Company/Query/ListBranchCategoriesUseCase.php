<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Company\Entity\BranchCategory;
use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Категории филиала для админки (включая неактивные) с их позициями.
 */
final class ListBranchCategoriesUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array{branch_id: int, items: list<array<string, mixed>>}
     */
    public function execute(int $branchId): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        return [
            'branch_id' => $branchId,
            'items' => array_map(
                fn (BranchCategory $category): array => $this->presenter->category(
                    $category,
                    $this->categories->findItemsByCategoryId($category->id()),
                ),
                $this->categories->listByBranch($branchId),
            ),
        ];
    }
}
