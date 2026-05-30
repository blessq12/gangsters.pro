<?php

namespace App\Application\Catalog\Presenter;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Entity\CategoryProduct;

final class AdminCategoryPresenter
{
    public function presentListItem(Category $category): array
    {
        return [
            'id' => $category->id(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'sort_order' => $category->sortOrder(),
            'is_active' => $category->isActive(),
            'updated_at' => $category->updatedAt()->format(DATE_ATOM),
        ];
    }

    public function presentDetail(Category $category, array $links): array
    {
        return [
            'category' => $this->presentListItem($category),
            'product_links' => array_map(
                static fn (CategoryProduct $link): array => [
                    'id' => $link->id(),
                    'product_id' => $link->productId(),
                    'sort_order' => $link->sortOrder(),
                ],
                $links,
            ),
        ];
    }
}
