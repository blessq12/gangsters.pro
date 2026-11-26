<?php

namespace App\Application\Menu\Query;

use App\Application\Menu\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;

/**
 * Одна категория филиала с позициями.
 */
final class GetBranchCategoryUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId, int $id): array
    {
        $this->branches->assertBranchExists($branchId);

        $category = $this->categories->findById($branchId, $id);
        if ($category === null) {
            throw new \InvalidArgumentException('Категория не найдена.');
        }

        return $this->presenter->category($category, $this->categories->findItemsByCategoryId($id));
    }
}
