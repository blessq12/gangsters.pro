<?php

namespace App\Domain\Delivery\Repository;

use App\Domain\Delivery\Entity\DeliveryConfiguration;

interface DeliveryConfigurationRepository
{
    public const SINGLETON_ID = 1;

    public function findPublic(): ?DeliveryConfiguration;

    /**
     * @param  list<array{id: string, name: string, fee_rubles: int, is_remote: bool}>  $zones
     */
    public function saveSettings(
        ?int $minOrderAmountKopecks,
        ?int $averageDeliveryTimeMinutes,
        ?int $kitchenId,
        array $zones,
    ): DeliveryConfiguration;
}
