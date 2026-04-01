<?php

namespace App\Domain\Product\Repository;

use App\Domain\Product\Entity\Product;

interface ProductRepository
{
    public function findById(int $id): ?Product;

    /**
     * @param int[] $ids
     * @return Product[]
     */
    public function findByIds(array $ids): array;

    /**
     * @param int[] $ids
     * @return Product[]
     */
    public function findActiveByIds(array $ids): array;

    /**
     * @return Product[]
     */
    public function findByCategoryId(int $categoryId): array;

    /**
     * Товары, недоступные для продажи в меню (в БД — не {@see Product::STATUS_ACTIVE}).
     *
     * @return Product[]
     */
    public function findNonActive(): array;

    public function save(Product $product): void;

    public function delete(Product $product): void;
}

