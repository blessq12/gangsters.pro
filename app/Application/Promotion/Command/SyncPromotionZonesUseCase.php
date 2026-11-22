<?php

namespace App\Application\Promotion\Command;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Core\Delivery\Tariff\Entity\PromotionZone;
use App\Domain\Core\Delivery\Tariff\Repository\PromotionZoneRepository;
use App\Domain\Core\Order\Port\OrderDeliveryTariffPort;

/**
 * Снимок зон филиала в PRM_zones. Delivery читается только через порт Order (ACL).
 */
final class SyncPromotionZonesUseCase
{
    public function __construct(
        private readonly OrderDeliveryTariffPort $deliveryTariffs,
        private readonly PromotionZoneRepository $zones,
        private readonly PromotionPolicyPresenter $presenter,
    ) {}

    /**
     * @return array{zones: list<array<string, mixed>>, count: int}
     */
    public function execute(int $branchId): array
    {
        $configuration = $this->deliveryTariffs->findConfigByBranch($branchId);
        if ($configuration === null) {
            throw new \InvalidArgumentException('Конфигурация доставки филиала не найдена.');
        }

        $snapshots = [];
        foreach ($configuration['zones'] as $zone) {
            $snapshots[] = new PromotionZone(
                id: (string) $zone['id'],
                name: (string) $zone['name'],
                feeKopecks: (int) $zone['fee_kopecks'],
                isRemote: (bool) $zone['is_remote'],
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
