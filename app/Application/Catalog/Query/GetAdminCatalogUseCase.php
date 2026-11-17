<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Catalog\Repository\CategoryRepository;
use App\Domain\Catalog\Repository\TagRepository;

/**
 * Полный каталог для админки: все категории, товары, наборы и теги.
 */
final class GetAdminCatalogUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogItemRepository $items,
        private readonly TagRepository $tags,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array{
     *     categories: list<array<string, mixed>>,
     *     products: list<array<string, mixed>>,
     *     sets: list<array<string, mixed>>,
     *     tags: list<array<string, mixed>>
     * }
     */
    public function execute(): array
    {
        $categoryRows = [];
        $categoryIdByProduct = [];

        foreach ($this->categories->findAllIncludingInactive() as $category) {
            $productIds = [];
            foreach ($this->categories->findItemsByCategoryId($category->id()) as $item) {
                if ($item->kind() !== CatalogItemKind::Product) {
                    continue;
                }
                $productIds[] = $item->catalogItemId();
                $categoryIdByProduct[$item->catalogItemId()] ??= $category->id();
            }

            $categoryRows[] = [
                ...$this->presenter->category($category),
                'product_ids' => $productIds,
            ];
        }

        $products = [];
        foreach ($this->items->listAllProducts() as $product) {
            $products[] = $this->presenter->adminProduct(
                $product,
                $categoryIdByProduct[$product->id()] ?? null,
            );
        }

        $sets = [];
        foreach ($this->items->listAllSets() as $set) {
            $sets[] = $this->presenter->adminSet($set);
        }

        $tags = [];
        foreach ($this->tags->findAllOrdered() as $tag) {
            $tags[] = $this->presenter->tag($tag);
        }

        return [
            'categories' => $categoryRows,
            'products' => $products,
            'sets' => $sets,
            'tags' => $tags,
        ];
    }
}
