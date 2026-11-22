<?php

namespace App\Domain\Core\Order\Repository;

use App\Domain\Core\Order\Entity\ProductPromotionRole;
use App\Domain\Core\Order\Enum\ProductPromotionRoleKind;

/**
 * Роли товаров в акциях филиала.
 */
interface ProductPromotionRoleRepository
{
    /**
     * @return list<ProductPromotionRole>
     */
    public function findAllByBranch(int $branchId): array;

    /**
     * @return list<ProductPromotionRole>
     */
    public function listPageByBranch(int $branchId, int $offset, int $limit): array;

    public function countByBranch(int $branchId): int;

    /**
     * @param  list<int>  $productIds
     * @return list<ProductPromotionRole>
     */
    public function findByBranchAndProductIds(int $branchId, array $productIds): array;

    /**
     * @return list<ProductPromotionRole>
     */
    public function findByBranchAndRole(int $branchId, ProductPromotionRoleKind $role): array;

    /**
     * Upsert роли товара (уникальность branch_id + product_id + role).
     */
    public function save(ProductPromotionRole $role): ProductPromotionRole;

    public function delete(int $branchId, int $productId, ProductPromotionRoleKind $role): void;

    /**
     * @param  list<int>  $ids
     * @return array<int, array{counts_as_roll: bool, complement_set: bool, paid_twin_product_id: ?int}>
     */
    public function findMetaByBranchAndProductIds(int $branchId, array $ids): array;
}
