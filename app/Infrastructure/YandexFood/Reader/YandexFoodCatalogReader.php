<?php

namespace App\Infrastructure\YandexFood\Reader;

use App\Application\YandexFood\Port\YandexFoodMenuCatalogPort;
use App\Domain\AggregatorIngress\Repository\PartnerCatalogBindingRepository;
use App\Domain\Catalog\Entity\Category;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Catalog\Repository\CategoryRepository;

final class YandexFoodCatalogReader implements YandexFoodMenuCatalogPort
{
    public function __construct(
        private readonly PartnerCatalogBindingRepository $bindings,
        private readonly CategoryRepository $categories,
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    public function partnerCode(): string
    {
        $code = config('yandex_food.partner_code', 'yandex-eda');

        return is_string($code) && $code !== '' ? $code : 'yandex-eda';
    }

    /**
     * @return array{
     *     categories: list<array{category: Category, has_items: bool}>,
     *     products: list<array{partner_sku: string, category_id: int, product: Product, sort_order: int}>
     * }
     */
    public function readCompositionCatalog(): array
    {
        $skuByProductId = $this->indexSkuByProductId();
        if ($skuByProductId === []) {
            return [
                'categories' => $this->mapCategoriesWithoutItems(),
                'products' => [],
            ];
        }

        $categoriesOutput = [];
        $productsOutput = [];
        $categoriesWithItems = [];

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
                if (! isset($skuByProductId[$productId])) {
                    continue;
                }

                $productIds[] = $productId;
                $sortOrders[$productId] = $link->sortOrder();
            }

            if ($productIds === []) {
                $categoriesOutput[] = [
                    'category' => $category,
                    'has_items' => false,
                ];

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
                    'partner_sku' => $skuByProductId[$productId],
                    'category_id' => $category->id(),
                    'product' => $product,
                    'sort_order' => $sortOrders[$productId] ?? 100,
                ];
            }

            $categoriesOutput[] = [
                'category' => $category,
                'has_items' => $categoryHasItems,
            ];
        }

        return [
            'categories' => $categoriesOutput,
            'products' => $productsOutput,
        ];
    }

    /**
     * @return list<string>
     */
    public function readUnavailablePartnerSkus(): array
    {
        $unavailable = [];

        foreach ($this->bindings->listByPartner($this->partnerCode()) as $binding) {
            $partnerSku = $binding['partner_sku'];
            $productId = $binding['product_id'];

            $product = $this->catalogItems->findProductById($productId);
            if (! $product instanceof Product) {
                $unavailable[] = $partnerSku;

                continue;
            }

            if (! $product->isActive() || $product->price()->amountRubles() <= 0) {
                $unavailable[] = $partnerSku;
            }
        }

        return array_values(array_unique($unavailable));
    }

    /**
     * @return array<int, string>
     */
    private function indexSkuByProductId(): array
    {
        $indexed = [];

        foreach ($this->bindings->listByPartner($this->partnerCode()) as $binding) {
            $indexed[$binding['product_id']] = $binding['partner_sku'];
        }

        return $indexed;
    }

    /**
     * @return list<array{category: Category, has_items: bool}>
     */
    private function mapCategoriesWithoutItems(): array
    {
        $output = [];

        foreach ($this->categories->findAllOrdered() as $category) {
            if (! $category->isActive()) {
                continue;
            }

            $output[] = [
                'category' => $category,
                'has_items' => false,
            ];
        }

        return $output;
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
