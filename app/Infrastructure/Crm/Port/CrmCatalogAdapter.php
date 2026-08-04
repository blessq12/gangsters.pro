<?php

namespace App\Infrastructure\Crm\Port;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Crm\Port\CrmCatalogAvailabilityPort;
use App\Domain\Crm\Port\CrmCatalogSnapshotsPort;

final class CrmCatalogAdapter implements CrmCatalogAvailabilityPort, CrmCatalogSnapshotsPort
{
    public function __construct(
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    public function activeCatalogKindsByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $kinds = [];

        foreach ($this->catalogItems->findActiveProductsByIds($ids) as $product) {
            $kinds[$product->id()] = CatalogItemKind::Product->value;
        }
        foreach ($this->catalogItems->findActiveSetsByIds($ids) as $set) {
            $kinds[$set->id()] = CatalogItemKind::Set->value;
        }

        return $kinds;
    }

    public function snapshotsByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $snapshots = [];

        foreach ($this->catalogItems->findActiveProductsByIds($ids) as $product) {
            $snapshots[$product->id()] = [
                'name' => $product->name(),
                'price_rubles' => $product->price()->amountRubles(),
            ];
        }
        foreach ($this->catalogItems->findActiveSetsByIds($ids) as $set) {
            $snapshots[$set->id()] = [
                'name' => $set->name(),
                'price_rubles' => $set->price()->amountRubles(),
            ];
        }

        return $snapshots;
    }
}
