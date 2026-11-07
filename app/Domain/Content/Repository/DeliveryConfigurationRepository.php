<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\DeliveryConfiguration;

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
        ?string $city,
        ?string $street,
        ?string $house,
        ?string $comment,
        ?string $searchLine,
        array $zones,
    ): DeliveryConfiguration;
}
