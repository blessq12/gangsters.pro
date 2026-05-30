<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\AdminProductPresenter;
use App\Application\Common\Exceptions\ApiException;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Product\Repository\ProductRepository;

final class GetAdminCategoryLayoutQuery
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly ProductRepository $products,
        private readonly AdminProductPresenter $productPresenter,
    ) {
    }

    public function execute(int $categoryId): array
    {
        $category = $this->categories->findById($categoryId);
        if ($category === null) {
            throw new ApiException('Category not found.', 404);
        }

        $links = $this->categories->findLinksByCategoryId($categoryId);
        usort(
            $links,
            static fn (CategoryProduct $a, CategoryProduct $b) => $a->sortOrder() <=> $b->sortOrder(),
        );

        $productIds = array_map(
            static fn (CategoryProduct $link) => $link->productId(),
            $links,
        );

        $products = $productIds === [] ? [] : $this->products->findByIds($productIds);
        $byId = [];
        foreach ($products as $product) {
            if ($product->id() !== null) {
                $byId[$product->id()] = $product;
            }
        }

        $items = [];
        foreach ($productIds as $id) {
            if (! isset($byId[$id])) {
                continue;
            }
            $items[] = $this->productPresenter->presentListItem($byId[$id]);
        }

        return [
            'category_id' => $categoryId,
            'category_name' => $category->name(),
            'products' => $items,
        ];
    }
}
