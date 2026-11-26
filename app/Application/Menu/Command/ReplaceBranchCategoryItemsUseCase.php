<?php

namespace App\Application\Menu\Command;

use App\Application\Menu\Presenter\BranchMenuStructurePresenter;
use App\Application\Menu\Service\BranchCategoryItemsValidator;
use App\Domain\Supporting\Menu\Port\MenuBranchLookupPort;
use App\Domain\Supporting\Menu\Repository\BranchCategoryRepository;

/**
 * Полная замена позиций категории филиала.
 */
final class ReplaceBranchCategoryItemsUseCase
{
    public function __construct(
        private readonly MenuBranchLookupPort $branches,
        private readonly BranchCategoryRepository $categories,
        private readonly BranchCategoryItemsValidator $itemsValidator,
        private readonly BranchMenuStructurePresenter $presenter,
    ) {}

    /**
     * @param  list<array{entry_kind: string, entry_id: int, sort_order: int}>  $items
     * @return array<string, mixed>
     */
    public function execute(int $branchId, int $categoryId, array $items): array
    {
        $this->branches->assertBranchExists($branchId);

        $category = $this->categories->findById($branchId, $categoryId);
        if ($category === null) {
            throw new \InvalidArgumentException('Категория не найдена.');
        }

        $this->itemsValidator->assertValid($branchId, $items);
        $this->categories->replaceItems($branchId, $categoryId, $items);

        return $this->presenter->category($category, $this->categories->findItemsByCategoryId($categoryId));
    }
}
