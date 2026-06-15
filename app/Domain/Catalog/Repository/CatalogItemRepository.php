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

    /**
     * @param  list<int>  $ids
     * @return array<int, string>
     */
    public function findProductNamesByIds(array $ids): array;

    /**
     * @param  list<int>  $ids
     * @return array<int, array{counts_as_roll: bool, gift_candidate: bool, complement_set: bool}>
     */
    public function findPromotionMetaByProductIds(array $ids): array;
}
