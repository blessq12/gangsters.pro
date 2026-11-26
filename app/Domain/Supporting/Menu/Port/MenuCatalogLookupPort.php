<?php

namespace App\Domain\Supporting\Menu\Port;

/**
 * ACL: Menu читает номенклатуру Catalog только через снимок своего порта.
 *
 * @phpstan-type MenuCatalogProductView array<string, mixed>
 */
interface MenuCatalogLookupPort
{
    public function productExists(int $productId): bool;

    /**
     * @param  list<int>  $ids
     * @return array<int, MenuCatalogProductView> keyed by product id
     */
    public function presentActiveProductsByIds(array $ids): array;

    /**
     * @param  list<int>  $ids
     * @return array<int, string> keyed by product id
     */
    public function findProductNamesByIds(array $ids): array;

    /**
     * @param  list<int>  $ids
     * @return array<int, int|null> weight by product id
     */
    public function findProductWeightsByIds(array $ids): array;
}
