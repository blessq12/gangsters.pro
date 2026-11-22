<?php

namespace App\Domain\Core\Delivery\Tariff\Repository;

use App\Domain\Core\Delivery\Tariff\Entity\DeliveryConfiguration;
use App\Domain\Core\Delivery\Tariff\ValueObject\DeliveryZone;

interface DeliveryConfigurationRepository
{
    public function findByBranch(int $branchId): ?DeliveryConfiguration;

    /**
     * @return list<DeliveryZone>
     */
    public function listZonesPageByBranch(int $branchId, int $offset, int $limit): array;

    public function countZonesByBranch(int $branchId): int;

    /**
     * @param  list<array{id: string, name: string, fee_rubles: int, is_remote: bool}>  $zones
     */
    public function saveSettingsForBranch(
        int $branchId,
        ?int $minOrderAmountKopecks,
        ?int $averageDeliveryTimeMinutes,
        array $zones,
    ): DeliveryConfiguration;
}
