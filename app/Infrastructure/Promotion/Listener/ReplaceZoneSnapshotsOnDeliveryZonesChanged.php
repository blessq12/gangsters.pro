<?php

namespace App\Infrastructure\Promotion\Listener;

use App\Domain\Delivery\Event\DeliveryZonesChanged;
use App\Domain\Promotion\Entity\PromotionZone;
use App\Domain\Promotion\Repository\PromotionZoneRepository;

/**
 * Delivery публикует актуальный набор зон; Promotion полностью заменяет свой снимок.
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

        $this->zones->replaceAll($snapshots);
    }
}
