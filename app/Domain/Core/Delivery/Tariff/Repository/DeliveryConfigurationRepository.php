<?php

namespace App\Domain\Core\Delivery\Tariff\Repository;

use App\Domain\Core\Delivery\Tariff\Entity\DeliveryConfiguration;
use App\Domain\Core\Delivery\Tariff\ValueObject\DeliveryZone;

interface DeliveryConfigurationRepository
{
    public const SINGLETON_ID = 1;

    public function findPublic(): ?DeliveryConfiguration;

    /**
     * @return list<DeliveryZone>
     */
    public function listZonesPage(int $offset, int $limit): array;

    public function countZones(): int;

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
