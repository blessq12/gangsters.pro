<?php

namespace App\Infrastructure\Branch\Adapter;

use App\Domain\Supporting\Branch\Entity\DeliveryConfiguration;
use App\Domain\Supporting\Branch\Port\BranchDeliveryConfigClonePort;
use App\Domain\Supporting\Branch\Repository\DeliveryConfigurationRepository;

final class EloquentBranchDeliveryConfigClonePort implements BranchDeliveryConfigClonePort
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
    ) {}

    public function export(int $branchId): ?array
    {
        $config = $this->delivery->findByBranch($branchId);
        if (! $config instanceof DeliveryConfiguration) {
            return null;
        }

        $zones = [];
        foreach ($config->zones() as $zone) {
            $zones[] = [
                'id' => $zone->id(),
                'name' => $zone->name(),
                'fee_rubles' => (int) round($zone->deliveryFeeKopecks() / 100),
                'is_remote' => $zone->isRemote(),
                'geometry' => $zone->geometry(),
            ];
        }

        return [
            'min_order_amount_kopecks' => $config->minOrderAmountKopecks(),
            'average_delivery_time_minutes' => $config->averageDeliveryTimeMinutes(),
            'zones' => $zones,
        ];
    }

    public function import(int $branchId, array $snapshot): void
    {
        $this->delivery->saveSettingsForBranch(
            branchId: $branchId,
            minOrderAmountKopecks: $snapshot['min_order_amount_kopecks'] ?? null,
            averageDeliveryTimeMinutes: $snapshot['average_delivery_time_minutes'] ?? null,
            zones: $snapshot['zones'] ?? [],
        );
    }

    public function existsForBranch(int $branchId): bool
    {
        return $this->delivery->findByBranch($branchId) instanceof DeliveryConfiguration;
    }
}
