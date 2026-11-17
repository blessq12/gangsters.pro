<?php

namespace App\Application\Promotion\Command;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Promotion\Entity\PromotionZone;
use App\Domain\Promotion\Repository\PromotionZoneRepository;

/**
 * Единственное место, где Promotion читает Delivery: снимок зон в PRM_zones.
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
    public function execute(): array
    {
        $configuration = $this->delivery->findPublic();
        if ($configuration === null) {
            throw new \InvalidArgumentException('Конфигурация доставки не найдена.');
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

        $this->zones->replaceAll($snapshots);

        $saved = $this->zones->findAll();

        return [
            'zones' => array_map(
                fn (PromotionZone $zone): array => $this->presenter->zone($zone),
                $saved,
            ),
            'count' => count($saved),
        ];
    }
}
