<?php

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Entity\Category;
use App\Domain\Catalog\Entity\CategoryItem;

interface CategoryRepository
{
    /**
     * @return list<Category>
     */
    public function findAllOrdered(): array;

    public function findById(int $id): ?Category;

    /**
     * @return list<CategoryItem>
     */
    public function findItemsByCategoryId(int $categoryId): array;
}
