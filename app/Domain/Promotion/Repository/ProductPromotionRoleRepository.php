<?php

namespace App\Domain\Promotion\Repository;

/**
 * Роли товаров в акциях (владелец — контекст Promotion).
 */
interface ProductPromotionRoleRepository
{
    /**
     * @param  list<int>  $ids
     * @return array<int, array{counts_as_roll: bool, complement_set: bool, paid_twin_product_id: ?int}>
     */
    public function findMetaByProductIds(array $ids): array;

    /**
     * @return list<int>
     */
    public function findPaidTwinProductIds(): array;

    /**
     * @return list<int>
     */
    public function findActiveGiftCandidateProductIds(): array;

    /**
     * @return list<int>
     */
    public function findActiveComplementSetProductIds(): array;
}
