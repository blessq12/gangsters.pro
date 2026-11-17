<?php

namespace App\Domain\Promotion\Repository;

use App\Domain\Promotion\Entity\ProductPromotionRole;
use App\Domain\Promotion\Enum\ProductPromotionRoleKind;

/**
 * Роли товаров в акциях (владелец — контекст Promotion, свои таблицы).
 */
interface ProductPromotionRoleRepository
{
    /**
     * @return list<ProductPromotionRole>
     */
    public function findAll(): array;

    /**
     * @param  list<int>  $productIds
     * @return list<ProductPromotionRole>
     */
    public function findByProductIds(array $productIds): array;

    /**
     * @return list<ProductPromotionRole>
     */
    public function findByRole(ProductPromotionRoleKind $role): array;

    /**
     * Upsert роли товара (уникальность product_id + role).
     */
    public function save(ProductPromotionRole $role): ProductPromotionRole;

    /**
     * @param  list<int>  $ids
     * @return array<int, array{counts_as_roll: bool, complement_set: bool, paid_twin_product_id: ?int}>
     */
    public function findMetaByProductIds(array $ids): array;
}
