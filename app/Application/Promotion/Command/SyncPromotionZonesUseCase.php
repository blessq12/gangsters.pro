<?php

namespace App\Application\Promotion\Command;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Delivery\Tariff\Entity\PromotionZone;
use App\Domain\Core\Delivery\Tariff\Repository\PromotionZoneRepository;

/**
 * Единственное место, где Promotion читает Delivery: снимок зон филиала в PRM_zones.
 */
final class SyncPromotionZonesUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly PromotionZoneRepository $zones,
        private readonly PromotionPolicyPresenter $presenter,
    ) {}

    /**
     * @return array{zones: list<array<string, mixed>>, count: int}
     */
    public function execute(int $branchId): array
    {
        $configuration = $this->delivery->findByBranch($branchId);
        if ($configuration === null) {
            throw new \InvalidArgumentException('Конфигурация доставки филиала не найдена.');
        }

        $snapshots = [];
        foreach ($configuration->zones() as $zone) {
            $snapshots[] = new PromotionZone(
                id: $zone->id(),
                name: $zone->name(),
                feeKopecks: $zone->deliveryFeeKopecks(),
                isRemote: $zone->isRemote(),
            );
        }

        $this->zones->replaceForBranch($branchId, $snapshots);

        $saved = $this->zones->findAllByBranch($branchId);

        return [
            'zones' => array_map(
                fn (PromotionZone $zone): array => $this->presenter->zone($zone),
                $saved,
            ),
            'count' => count($saved),
        ];
    }
}
