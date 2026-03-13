<?php

namespace App\Domain\Category\Repository;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Entity\CategoryProduct;

interface CategoryRepository
{
    public function findById(int $id): ?Category;

    /**
     * @return Category[]
     */
    public function findAllOrdered(): array;

    public function save(Category $category): void;

    public function delete(Category $category): void;

    /**
     * @return CategoryProduct[]
     */
    public function findLinksByCategoryId(int $categoryId): array;

    public function saveLink(CategoryProduct $link): void;

    public function deleteLink(CategoryProduct $link): void;
}

