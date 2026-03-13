<?php

namespace App\Application\Category\Presenter;

use App\Domain\Category\Entity\Category;

final class CategoryPresenter
{
    public function present(Category $category): array
    {
        return [
            'id' => $category->id(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'sort_order' => $category->sortOrder(),
            'is_active' => $category->isActive(),
            'created_at' => $category->createdAt()->format(DATE_ATOM),
            'updated_at' => $category->updatedAt()->format(DATE_ATOM),
        ];
    }
}

