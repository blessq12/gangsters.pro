<?php

namespace App\Infrastructure\Promotion\Repository;

use App\Domain\Core\Order\Entity\ProductPromotionRole;
use App\Domain\Core\Order\Enum\ProductPromotionRoleKind;
use App\Domain\Core\Order\Repository\ProductPromotionRoleRepository;
use App\Infrastructure\Promotion\Mapper\ProductPromotionRoleMapper;
use App\Infrastructure\Promotion\Model\PRM_ProductRole;

final class EloquentProductPromotionRoleRepository implements ProductPromotionRoleRepository
{
    public function __construct(
        private readonly ProductPromotionRoleMapper $mapper,
    ) {}

    public function findAllByBranch(int $branchId): array
    {
        return PRM_ProductRole::query()
            ->where('branch_id', $branchId)
            ->orderBy('role')
            ->orderBy('catalog_item_id')
            ->get()
            ->map(fn (PRM_ProductRole $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listPageByBranch(int $branchId, int $offset, int $limit): array
    {
        return PRM_ProductRole::query()
            ->where('branch_id', $branchId)
            ->orderBy('role')
            ->orderBy('catalog_item_id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (PRM_ProductRole $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function countByBranch(int $branchId): int
    {
        return (int) PRM_ProductRole::query()
            ->where('branch_id', $branchId)
            ->count();
    }

    public function findByBranchAndProductIds(int $branchId, array $productIds): array
    {
        $ids = $this->normalizeIds($productIds);
        if ($ids === []) {
            return [];
        }

        return PRM_ProductRole::query()
            ->where('branch_id', $branchId)
            ->whereIn('catalog_item_id', $ids)
            ->orderBy('role')
            ->orderBy('catalog_item_id')
            ->get()
            ->map(fn (PRM_ProductRole $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findByBranchAndRole(int $branchId, ProductPromotionRoleKind $role): array
    {
        return PRM_ProductRole::query()
            ->where('branch_id', $branchId)
            ->where('role', $role->value)
            ->orderBy('catalog_item_id')
            ->get()
            ->map(fn (PRM_ProductRole $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(ProductPromotionRole $role): ProductPromotionRole
    {
        $row = PRM_ProductRole::query()->firstOrNew([
            'branch_id' => $role->branchId(),
            'catalog_item_id' => $role->productId(),
            'role' => $role->role()->value,
        ]);

        $row->fill([
            'name_snapshot' => $role->name(),
            'price_kopecks_snapshot' => $role->priceKopecks(),
            'paid_twin_catalog_item_id' => $role->paidTwinProductId(),
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }

    public function delete(int $branchId, int $productId, ProductPromotionRoleKind $role): void
    {
        PRM_ProductRole::query()
            ->where('branch_id', $branchId)
            ->where('catalog_item_id', $productId)
            ->where('role', $role->value)
            ->delete();
    }

    public function findMetaByBranchAndProductIds(int $branchId, array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        $meta = [];
        foreach ($ids as $id) {
            $meta[$id] = [
                'counts_as_roll' => false,
                'complement_set' => false,
                'paid_twin_product_id' => null,
            ];
        }

        foreach ($this->findByBranchAndProductIds($branchId, $ids) as $role) {
            $productId = $role->productId();
            if ($role->countsAsRoll()) {
                $meta[$productId]['counts_as_roll'] = true;
            }
            if ($role->isComplementSet()) {
                $meta[$productId]['complement_set'] = true;
                $meta[$productId]['paid_twin_product_id'] = $role->paidTwinProductId();
            }
        }

        return $meta;
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = [];
        foreach ($ids as $id) {
            if (! is_numeric($id)) {
                continue;
            }
            $intId = (int) $id;
            if ($intId > 0) {
                $normalized[] = $intId;
            }
        }

        return array_values(array_unique($normalized));
    }
}
