<?php

namespace App\Application\Menu\Query;

use App\Application\Menu\Presenter\BranchMenuStructurePresenter;
use App\Domain\Supporting\Menu\Entity\BranchCategory;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;

/**
 * Категории филиала для админки (включая неактивные) с их позициями.
 */
final class ListBranchCategoriesUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @return array{branch_id: int, items: list<array<string, mixed>>}
     */
    public function execute(int $branchId): array
    {
        $this->branches->assertBranchExists($branchId);

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
