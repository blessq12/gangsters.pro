<?php

namespace App\Application\Catalog\Presenter;

use App\Domain\Category\Entity\Category;
use App\Domain\Product\Entity\Product;

final class AdminCatalogOverviewPresenter
{
    /**
     * @param  Product[]  $products
     */
    public function presentCategoryNode(Category $category, array $products): array
    {
        return [
            'category' => [
                'id' => $category->id(),
                'name' => $category->name(),
                'slug' => $category->slug(),
                'is_active' => $category->isActive(),
                'sort_order' => $category->sortOrder(),
            ],
            'products' => array_map(
                static fn (Product $product): array => [
                    'id' => $product->id(),
                    'name' => $product->name(),
                    'status' => $product->status(),
                ],
                $products,
            ),
        ];
    }
}
