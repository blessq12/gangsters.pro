<?php

namespace App\Application\Catalog\Query;

use App\Application\Category\Presenter\CategoryPresenter;
use App\Application\Product\Presenter\ProductPresenter;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;

final class GetCatalogTreeUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ProductRepository $products,
        private readonly CategoryPresenter $categoryPresenter,
        private readonly ProductPresenter $productPresenter,
    ) {
    }

    public function execute(): array
    {
        $result = [
            'categories' => [],
        ];

        $categories = $this->categories->findAllOrdered();

        foreach ($categories as $category) {
            if (!$category->isActive()) {
                continue;
            }

            $node = $this->buildCategoryNode($category);
            if (!empty($node['products'])) {
                $result['categories'][] = $node;
            }
        }

        return $result;
    }

    private function buildCategoryNode(Category $category): array
    {
        $links = $this->categories->findLinksByCategoryId($category->id());

        // Собираем id продуктов в порядке sortOrder
        usort(
            $links,
            static fn (CategoryProduct $a, CategoryProduct $b) => $a->sortOrder() <=> $b->sortOrder(),
        );

        $productIds = array_map(
            static fn (CategoryProduct $link) => $link->productId(),
            $links,
        );

        if ($productIds === []) {
            return [
                'category' => $this->categoryPresenter->present($category),
                'products' => [],
            ];
        }

        $products = $this->products->findByIds($productIds);

        // Индексируем продукты по id для восстановления порядка
        $productsById = [];
        foreach ($products as $product) {
            if ($product instanceof Product && $product->status() === Product::STATUS_ACTIVE) {
                $productsById[$product->id()] = $product;
            }
        }

        $orderedProducts = [];
        foreach ($productIds as $id) {
            if (isset($productsById[$id])) {
                $orderedProducts[] = $productsById[$id];
            }
        }

        return [
            'category' => $this->categoryPresenter->present($category),
            'products' => array_map(
                fn (Product $product) => $this->productPresenter->present($product),
                $orderedProducts,
            ),
        ];
    }
}

