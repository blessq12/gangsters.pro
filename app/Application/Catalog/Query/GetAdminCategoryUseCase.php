<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Catalog\Repository\CategoryRepository;

final class GetAdminCategoryUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogItemRepository $items,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $category = $this->categories->findById($id);
        if ($category === null) {
            throw new \InvalidArgumentException('Категория не найдена.');
        }

        $links = $this->categories->findItemsByCategoryId($id);
        $itemIds = array_map(static fn ($link): int => $link->catalogItemId(), $links);
        $names = $this->items->findProductNamesByIds($itemIds);

        // Sets names via findSetById (includes empty drafts).
        foreach ($itemIds as $itemId) {
            if (isset($names[$itemId])) {
                continue;
            }
            $set = $this->items->findSetById($itemId);
            if ($set !== null) {
                $names[$itemId] = $set->name();
            }
        }

        $items = [];
        foreach ($links as $link) {
            $items[] = [
                'id' => $link->catalogItemId(),
                'kind' => $link->kind()->value,
                'name' => $names[$link->catalogItemId()] ?? ('#'.$link->catalogItemId()),
                'sort_order' => $link->sortOrder(),
            ];
        }

        return $this->presenter->adminCategoryDetail($category, $items);
    }
}