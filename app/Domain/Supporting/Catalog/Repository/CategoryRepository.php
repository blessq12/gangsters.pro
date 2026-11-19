<?php

namespace App\Domain\Supporting\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\Category;
use App\Domain\Supporting\Catalog\Entity\CategoryItem;

interface CategoryRepository
{
    /**
     * @return list<Category>
     */
    public function findAllOrdered(): array;

    /**
     * Every category, including inactive ones.
     *
     * @return list<Category>
     */
    public function findAllIncludingInactive(): array;

    public function findById(int $id): ?Category;

    public function save(?int $id, string $name, bool $isActive, bool $isAccompanying): Category;

    /**
     * @return list<CategoryItem>
     */
    public function findItemsByCategoryId(int $categoryId): array;
}
