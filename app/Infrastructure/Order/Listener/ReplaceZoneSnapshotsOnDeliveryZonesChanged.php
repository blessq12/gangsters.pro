<?php

namespace App\Infrastructure\Order\Listener;

use App\Domain\Core\Delivery\Event\DeliveryZonesChanged;
use App\Domain\Core\Delivery\Tariff\Entity\PromotionZone;
use App\Domain\Core\Delivery\Tariff\Repository\PromotionZoneRepository;

/**
 * Delivery публикует зоны; Order заменяет снимок в PRM_zones (не участвует в продаже).
 */
final class ReplaceZoneSnapshotsOnDeliveryZonesChanged
{
    public function __construct(
        private readonly PromotionZoneRepository $zones,
    ) {}

    public function handle(DeliveryZonesChanged $event): void
    {
        $snapshots = [];
        foreach ($event->zones as $zone) {
            $snapshots[] = new PromotionZone(
                id: $zone['id'],
                name: $zone['name'],
                feeKopecks: $zone['fee_kopecks'],
                isRemote: $zone['is_remote'],
            );
        }

        $this->zones->replaceForBranch($event->branchId, $snapshots);
    }
}
