<?php

namespace App\Infrastructure\YandexFood\Reader;

use App\Application\YandexFood\Port\YandexFoodMenuCatalogPort;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Catalog\Repository\CategoryRepository;

final class YandexFoodCatalogReader implements YandexFoodMenuCatalogPort
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogItemRepository $catalogItems,
    ) {}

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
                    'id' => $product->id(),
                    'category_id' => $category->id(),
                    'name' => $product->name(),
                    'description' => $product->description() ?? '',
                    'price_rubles' => $product->price()->amountRubles(),
                    'sort_order' => $sortOrders[$productId] ?? 100,
                    'image_paths' => $this->imagePaths($product),
                ];
            }

            if ($categoryHasItems) {
                $categoriesOutput[] = [
                    'id' => $category->id(),
                    'name' => $category->name(),
                    'sort_order' => $category->sortOrder(),
                    'has_items' => true,
                ];
            }
        }

        return [
            'categories' => $categoriesOutput,
            'products' => $productsOutput,
        ];
    }

    public function readUnavailableProductIds(): array
    {
        return array_map(
            static fn (int $id): string => (string) $id,
            $this->catalogItems->findArchivedProductIds(),
        );
    }

    /**
     * @return list<string>
     */
    private function imagePaths(Product $product): array
    {
        $paths = [];

        foreach ($product->images() as $image) {
            $path = $image->path();
            if ($path === '') {
                continue;
            }

            $paths[] = $path;
        }

        return $paths;
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
