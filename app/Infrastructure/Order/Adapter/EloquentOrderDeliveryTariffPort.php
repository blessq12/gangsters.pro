<?php

namespace App\Infrastructure\Order\Adapter;

use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Delivery\Tariff\ValueObject\DeliveryZone;
use App\Domain\Core\Order\Port\OrderDeliveryTariffPort;
use App\Shared\Geo\DeliveryZoneMatcher;

/**
 * ACL: Order читает тарифы доставки через DeliveryConfigurationRepository в Infrastructure.
 */
final class EloquentOrderDeliveryTariffPort implements OrderDeliveryTariffPort
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $deliveryConfigs,
    ) {}

    public function findConfigByBranch(int $branchId): ?array
    {
        $config = $this->deliveryConfigs->findByBranch($branchId);
        if ($config === null) {
            return null;
        }

        $zones = [];
        foreach ($config->zones() as $zone) {
            $zones[] = $this->zoneSnapshot($zone);
        }

        return [
            'min_order_kopecks' => $config->minOrderAmountKopecks(),
            'zones' => $zones,
        ];
    }

    public function matchZoneByCoordinates(int $branchId, ?float $latitude, ?float $longitude): ?array
    {
        $config = $this->deliveryConfigs->findByBranch($branchId);
        if ($config === null) {
            return null;
        }

        $matched = DeliveryZoneMatcher::match($config->zones(), $latitude, $longitude);

        return $matched !== null ? $this->zoneSnapshot($matched) : null;
    }

    public function findZoneById(int $branchId, string $zoneId): ?array
    {
        $config = $this->deliveryConfigs->findByBranch($branchId);
        if ($config === null) {
            return null;
        }

        foreach ($config->zones() as $zone) {
            if ($zone->id() === $zoneId) {
                return $this->zoneSnapshot($zone);
            }
        }

        return null;
    }

    /**
     * @return array{id: string, name: string, fee_kopecks: int, is_remote: bool}
     */
    private function zoneSnapshot(DeliveryZone $zone): array
    {
        return [
            'id' => $zone->id(),
            'name' => $zone->name(),
            'fee_kopecks' => $zone->deliveryFeeKopecks(),
            'is_remote' => $zone->isRemote(),
        ];
    }
}
