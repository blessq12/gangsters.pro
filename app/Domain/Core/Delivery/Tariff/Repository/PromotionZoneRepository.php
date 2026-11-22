<?php

namespace App\Domain\Core\Delivery\Tariff\Repository;

use App\Domain\Core\Delivery\Tariff\Entity\PromotionZone;

/**
 * Снимок зон доставки для Promotion (per-branch).
 */
interface PromotionZoneRepository
{
    /**
     * @return list<PromotionZone>
     */
    public function findAllByBranch(int $branchId): array;

    /**
     * @return list<PromotionZone>
     */
    public function listPageByBranch(int $branchId, int $offset, int $limit): array;

    public function countByBranch(int $branchId): int;

    public function findByBranchAndId(int $branchId, string $id): ?PromotionZone;

    /**
     * Полная замена снимка зон филиала.
     *
     * @param  list<PromotionZone>  $zones
     */
    public function replaceForBranch(int $branchId, array $zones): void;
}
