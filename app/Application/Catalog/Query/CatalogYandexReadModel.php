<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Contracts\CatalogYandexReadModelContract;
use App\Application\Integrations\Contracts\IntegrationMenuExportReadPort;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;
use App\Support\Money;

final class CatalogYandexReadModel implements CatalogYandexReadModelContract, IntegrationMenuExportReadPort
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
    ) {
    }

    public function getActiveMenuBlocks(): array
    {
        $blocks = [];

        foreach ($this->categories->findAllOrdered() as $category) {
            if (! $category->isActive()) {
                continue;
            }

            $links = $this->categories->findLinksByCategoryId($category->id());
            usort(
                $links,
                static fn (CategoryProduct $a, CategoryProduct $b) => $a->sortOrder() <=> $b->sortOrder(),
            );

            $productIds = array_map(
                static fn (CategoryProduct $link) => $link->productId(),
                $links,
            );
            if ($productIds === []) {
                continue;
            }

            $products = $this->products->findActiveByIds($productIds);
            $productsById = [];
            foreach ($products as $product) {
                if ($product instanceof Product && $product->id() !== null) {
                    $productsById[$product->id()] = $product;
                }
            }

            $lines = [];
            foreach ($links as $link) {
                $product = $productsById[$link->productId()] ?? null;
                if (! ($product instanceof Product)) {
                    continue;
                }

                $price = $product->price();
                if ($price === null || $price <= 0) {
                    continue;
                }

                $lines[] = [
                    'product' => [
                        'id' => (string) $product->id(),
                        'name' => $product->name(),
                        'description' => $product->description(),
                        'priceRubles' => Money::kopecksToApiRubles($price),
                    ],
                    'sortOrder' => $link->sortOrder(),
                ];
            }

            if ($lines === []) {
                continue;
            }

            $blocks[] = [
                'category' => [
                    'id' => (string) $category->id(),
                    'name' => $category->name(),
                    'sortOrder' => $category->sortOrder(),
                ],
                'lines' => $lines,
            ];
        }

        return $blocks;
    }

    public function getUnavailableProductIds(): array
    {
        $ids = [];
        foreach ($this->products->findNonActive() as $product) {
            if (! ($product instanceof Product) || $product->id() === null) {
                continue;
            }
            $ids[] = (string) $product->id();
        }

        return array_values(array_unique($ids));
    }
}
