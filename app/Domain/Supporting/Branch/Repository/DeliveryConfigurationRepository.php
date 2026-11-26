<?php

namespace App\Domain\Supporting\Branch\Repository;

use App\Domain\Supporting\Branch\Entity\DeliveryConfiguration;
use App\Domain\Supporting\Branch\ValueObject\DeliveryZone;

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
