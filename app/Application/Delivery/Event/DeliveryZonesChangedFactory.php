<?php

namespace App\Application\Delivery\Event;

use App\Domain\Core\Delivery\Event\DeliveryZonesChanged;
use App\Domain\Core\Delivery\Tariff\Entity\DeliveryConfiguration;

/**
 * Снимок зон для события; событие создаётся, только если снимок изменился.
 */
final class DeliveryZonesChangedFactory
{
    /**
     * @return list<array{id: string, name: string, fee_kopecks: int, is_remote: bool}>
     */
    public function snapshot(?DeliveryConfiguration $configuration): array
    {
        if ($configuration === null) {
            return [];
        }

        $zones = [];
        foreach ($configuration->zones() as $zone) {
            $zones[] = [
                'id' => $zone->id(),
                'name' => $zone->name(),
                'fee_kopecks' => $zone->deliveryFeeKopecks(),
                'is_remote' => $zone->isRemote(),
            ];
        }

        return $zones;
    }

    /**
     * @param  list<array{id: string, name: string, fee_kopecks: int, is_remote: bool}>  $before
     */
    public function createIfChanged(array $before, DeliveryConfiguration $after): ?DeliveryZonesChanged
    {
        $branchId = $after->branchId();
        if ($branchId === null) {
            return null;
        }

        $snapshot = $this->snapshot($after);

        return $snapshot === $before ? null : new DeliveryZonesChanged(branchId: $branchId, zones: $snapshot);
    }
}
