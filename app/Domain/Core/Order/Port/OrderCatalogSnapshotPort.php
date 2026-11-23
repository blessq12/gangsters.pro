<?php

namespace App\Domain\Core\Order\Port;

/**
 * Снимок каталога для оформления заказа. Order Domain не импортирует Catalog.
 *
 * @phpstan-type OrderProductSnapshot array{
 *     id: int,
 *     name: string,
 *     sku: string|null,
 *     is_roll: bool,
 *     images: list<string>
 * }
 */
interface OrderCatalogSnapshotPort
{
    /**
     * @param  list<int>  $ids
     * @return array<int, OrderProductSnapshot> keyed by product id
     */
    public function findActiveProductsByIds(array $ids): array;

    /**
     * @param  list<int>  $ids
     * @return array<int, string> keyed by product id
     */
    public function findProductNamesByIds(array $ids): array;
}
