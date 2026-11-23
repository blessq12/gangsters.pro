<?php

namespace App\Infrastructure\Order\Adapter;

use App\Domain\Core\Order\Port\OrderBranchMenuSnapshotPort;
use App\Domain\Supporting\Company\Entity\BranchSet;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Domain\Supporting\Company\Repository\BranchSetRepository;

/**
 * ACL: Order читает меню/сеты филиала через Company-репозитории в Infrastructure.
 */
final class EloquentOrderBranchMenuSnapshotPort implements OrderBranchMenuSnapshotPort
{
    public function __construct(
        private readonly BranchMenuRepository $menu,
        private readonly BranchSetRepository $branchSets,
    ) {}

    public function listAvailableByBranch(int $branchId): array
    {
        $items = [];
        foreach ($this->menu->listAvailableByBranch($branchId) as $item) {
            $items[] = [
                'catalog_item_id' => $item->catalogItemId(),
                'is_available' => $item->isAvailable(),
                'price_kopecks' => $item->priceKopecks(),
            ];
        }

        return $items;
    }

    public function findByBranchAndItemIds(int $branchId, array $catalogItemIds): array
    {
        $indexed = [];
        foreach ($this->menu->findByBranchAndItemIds($branchId, $catalogItemIds) as $catalogItemId => $item) {
            $indexed[(int) $catalogItemId] = [
                'catalog_item_id' => $item->catalogItemId(),
                'is_available' => $item->isAvailable(),
                'price_kopecks' => $item->priceKopecks(),
            ];
        }

        return $indexed;
    }

    public function findAvailableSetsByBranchAndIds(int $branchId, array $ids): array
    {
        $indexed = [];
        foreach ($this->branchSets->findAvailableByBranchAndIds($branchId, $ids) as $set) {
            $indexed[$set->id()] = $this->setSnapshot($set);
        }

        return $indexed;
    }

    public function listAvailableSetsByBranch(int $branchId): array
    {
        $indexed = [];
        foreach ($this->branchSets->listByBranch($branchId) as $set) {
            if (! $set->isAvailable() || $set->lines() === []) {
                continue;
            }
            $indexed[$set->id()] = $this->setSnapshot($set);
        }

        return $indexed;
    }

    /**
     * @return array{id: int, name: string, is_available: bool, price_kopecks: int, composition: list<array{product_id: int, quantity: int}>}
     */
    private function setSnapshot(BranchSet $set): array
    {
        $composition = [];
        foreach ($set->lines() as $line) {
            $composition[] = [
                'product_id' => $line->catalogItemId(),
                'quantity' => $line->quantity(),
            ];
        }

        return [
            'id' => $set->id(),
            'name' => $set->name(),
            'is_available' => $set->isAvailable(),
            'price_kopecks' => $set->priceKopecks(),
            'composition' => $composition,
        ];
    }
}
