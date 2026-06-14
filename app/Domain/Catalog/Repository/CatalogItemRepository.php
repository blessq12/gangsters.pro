<?php

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;

interface CatalogItemRepository
{
    public function findProductById(int $id): ?Product;

    public function findSetById(int $id): ?ProductSet;

    /**
     * @param  list<int>  $ids
     * @return list<Product>
     */
    public function findActiveProductsByIds(array $ids): array;

    /**
     * @param  list<int>  $ids
     * @return list<ProductSet>
     */
    public function findActiveSetsByIds(array $ids): array;
}
