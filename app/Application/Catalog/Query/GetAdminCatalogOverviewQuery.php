<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\AdminCatalogOverviewPresenter;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;

final class GetAdminCatalogOverviewQuery
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ProductRepository $products,
        private readonly AdminCatalogOverviewPresenter $presenter,
    ) {
    }

    public function execute(): array
    {
        $nodes = [];

        foreach ($this->categories->findAllOrdered() as $category) {
            $links = $this->categories->findLinksByCategoryId((int) $category->id());
            usort(
                $links,
                static fn (CategoryProduct $a, CategoryProduct $b) => $a->sortOrder() <=> $b->sortOrder(),
            );

            $productIds = array_map(
                static fn (CategoryProduct $link) => $link->productId(),
                $links,
            );

            $products = $productIds === []
                ? []
                : $this->orderProducts($this->products->findByIds($productIds), $productIds);

            $nodes[] = $this->presenter->presentCategoryNode($category, $products);
        }

        return ['categories' => $nodes];
    }

    /**
     * @param  Product[]  $products
     * @param  int[]  $productIds
     * @return Product[]
     */
    private function orderProducts(array $products, array $productIds): array
    {
        $byId = [];
        foreach ($products as $product) {
            if ($product->id() !== null) {
                $byId[$product->id()] = $product;
            }
        }

        $ordered = [];
        foreach ($productIds as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }
}
