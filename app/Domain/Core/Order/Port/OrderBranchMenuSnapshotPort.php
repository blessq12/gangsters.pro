<?php

namespace App\Domain\Core\Order\Port;

/**
 * Снимок меню и сетов филиала для оформления заказа. Order Domain не импортирует Company.
 *
 * @phpstan-type OrderMenuItemSnapshot array{
 *     catalog_item_id: int,
 *     is_available: bool,
 *     price_kopecks: int
 * }
 * @phpstan-type OrderBranchSetSnapshot array{
 *     id: int,
 *     name: string,
 *     is_available: bool,
 *     price_kopecks: int,
 *     composition: list<array{product_id: int, quantity: int}>
 * }
 */
interface OrderBranchMenuSnapshotPort
{
    /**
     * @return list<OrderMenuItemSnapshot>
     */
    public function listAvailableByBranch(int $branchId): array;

    /**
     * @param  list<int>  $catalogItemIds
     * @return array<int, OrderMenuItemSnapshot> keyed by catalog_item_id
     */
    public function findByBranchAndItemIds(int $branchId, array $catalogItemIds): array;

    /**
     * @param  list<int>  $ids
     * @return array<int, OrderBranchSetSnapshot> keyed by set id
     */
    public function findAvailableSetsByBranchAndIds(int $branchId, array $ids): array;

    /**
     * @return array<int, OrderBranchSetSnapshot> keyed by set id
     */
    public function listAvailableSetsByBranch(int $branchId): array;

    /**
     * Product id из ролевой категории меню филиала (complement|gift).
     *
     * @return list<int>
     */
    public function listProductIdsByRole(int $branchId, string $role): array;
}
