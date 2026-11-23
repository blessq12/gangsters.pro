<?php

namespace App\Application\Company\Command;

use App\Application\Company\Presenter\BranchMenuStructurePresenter;
use App\Application\Company\Service\BranchCategoryItemsValidator;
use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;

/**
 * Полная замена позиций категории филиала.
 */
final class ReplaceBranchCategoryItemsUseCase
{
    public function __construct(
        private readonly BranchRepository $branches,
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
        if ($this->branches->findById($branchId) === null) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $category = $this->categories->findById($branchId, $categoryId);
        if ($category === null) {
            throw new \InvalidArgumentException('Категория не найдена.');
        }

        $this->itemsValidator->assertValid($branchId, $items);
        $this->categories->replaceItems($branchId, $categoryId, $items);

        return $this->presenter->category($category, $this->categories->findItemsByCategoryId($categoryId));
    }
}
