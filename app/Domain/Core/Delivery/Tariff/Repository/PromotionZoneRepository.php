<?php

namespace App\Domain\Core\Delivery\Tariff\Repository;

use App\Domain\Core\Delivery\Tariff\Entity\PromotionZone;

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
