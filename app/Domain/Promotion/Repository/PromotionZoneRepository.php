<?php

namespace App\Domain\Promotion\Repository;

use App\Domain\Promotion\Entity\PromotionZone;

interface PromotionZoneRepository
{
    /**
     * @return list<PromotionZone>
     */
    public function findAll(): array;

    public function findById(string $id): ?PromotionZone;

    /**
     * Полная замена снимка зон.
     *
     * @param  list<PromotionZone>  $zones
     */
    public function replaceAll(array $zones): void;
}
