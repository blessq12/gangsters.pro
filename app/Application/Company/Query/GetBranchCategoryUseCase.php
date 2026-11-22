<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Одна категория филиала с позициями.
 */
final class GetBranchCategoryUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId, int $id): array
    {
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $category = $this->categories->findById($branchId, $id);
        if ($category === null) {
            throw new \InvalidArgumentException('Категория не найдена.');
        }

        return $this->presenter->category($category, $this->categories->findItemsByCategoryId($id));
    }
}
