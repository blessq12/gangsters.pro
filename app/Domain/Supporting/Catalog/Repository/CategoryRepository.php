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

    /**
     * @return list<Category>
     */
    public function listPage(int $offset, int $limit): array;

    public function countAll(): int;

    public function findById(int $id): ?Category;

    public function save(?int $id, string $name, bool $isActive, bool $isAccompanying): Category;

    public function delete(int $id): void;

    /**
     * Полная перестановка порядка категорий (индекс = sort_order).
     *
     * @param  list<int>  $orderedIds
     */
    public function reorder(array $orderedIds): void;

    /**
     * @return list<CategoryItem>
     */
    public function findItemsByCategoryId(int $categoryId): array;

    /**
     * Полная замена состава категории (порядок = sort_order).
     *
     * @param  list<array{item_id: int, sort_order: int}>  $items
     */
    public function replaceItems(int $categoryId, array $items): void;
}
