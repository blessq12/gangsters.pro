<?php

namespace App\Infrastructure\YandexFood\Reader;

use App\Application\YandexFood\Port\YandexFoodMenuCatalogPort;
use App\Domain\Catalog\Entity\Category;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Catalog\Repository\CategoryRepository;
use App\Infrastructure\Catalog\Model\PRD_Product;

final class YandexFoodCatalogReader implements YandexFoodMenuCatalogPort
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    /**
     * @return array{
     *     categories: list<array{category: Category, has_items: bool}>,
     *     products: list<array{category_id: int, product: Product, sort_order: int}>
     * }
     */
    public function readCompositionCatalog(): array
    {
        $categoriesOutput = [];
        $productsOutput = [];

        foreach ($this->categories->findAllOrdered() as $category) {
            if (! $category->isActive()) {
                continue;
            }

            $productIds = [];
            $sortOrders = [];

            foreach ($this->categories->findItemsByCategoryId($category->id()) as $link) {
                if ($link->kind() !== CatalogItemKind::Product) {
                    continue;
                }

                $productId = $link->catalogItemId();
                $productIds[] = $productId;
                $sortOrders[$productId] = $link->sortOrder();
            }

            if ($productIds === []) {
                continue;
            }

            $productsById = $this->indexProducts(
                $this->catalogItems->findActiveProductsByIds($productIds),
            );

            $categoryHasItems = false;

            foreach ($productIds as $productId) {
                $product = $productsById[$productId] ?? null;
                if (! $product instanceof Product) {
                    continue;
                }

                if ($product->price()->amountRubles() <= 0) {
                    continue;
                }

                $categoryHasItems = true;
                $productsOutput[] = [
                    'category_id' => $category->id(),
                    'product' => $product,
                    'sort_order' => $sortOrders[$productId] ?? 100,
                ];
            }

            if ($categoryHasItems) {
                $categoriesOutput[] = [
                    'category' => $category,
                    'has_items' => true,
                ];
            }
        }

        return [
            'categories' => $categoriesOutput,
            'products' => $productsOutput,
        ];
    }

    /**
     * @return list<string>
     */
    public function readUnavailableProductIds(): array
    {
        return PRD_Product::query()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Archived->value)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->all();
    }

    /**
     * @param  list<Product>  $products
     * @return array<int, Product>
     */
    private function indexProducts(array $products): array
    {
        $indexed = [];

        foreach ($products as $product) {
            $indexed[$product->id()] = $product;
        }

        return $indexed;
    }
}
