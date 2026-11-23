<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Supporting\Company\Entity\BranchMenuItem;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Infrastructure\Company\Mapper\BranchMenuMapper;
use App\Infrastructure\Company\Model\CMP_BranchMenuItem;
use Illuminate\Support\Facades\DB;

final class EloquentBranchMenuRepository implements BranchMenuRepository
{
    public function __construct(
        private readonly BranchMenuMapper $mapper,
    ) {}

    public function listByBranch(int $branchId): array
    {
        return CMP_BranchMenuItem::query()
            ->where('branch_id', $branchId)
            ->orderBy('catalog_item_id')
            ->get()
            ->map(fn (CMP_BranchMenuItem $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function listAvailableByBranch(int $branchId): array
    {
        return CMP_BranchMenuItem::query()
            ->where('branch_id', $branchId)
            ->where('is_available', true)
            ->orderBy('catalog_item_id')
            ->get()
            ->map(fn (CMP_BranchMenuItem $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findByBranchAndItemIds(int $branchId, array $catalogItemIds): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($id) => (int) $id, $catalogItemIds),
            static fn (int $id): bool => $id > 0,
        )));
        if ($ids === []) {
            return [];
        }

        $out = [];
        $rows = CMP_BranchMenuItem::query()
            ->where('branch_id', $branchId)
            ->whereIn('catalog_item_id', $ids)
            ->get();
        foreach ($rows as $row) {
            $item = $this->mapper->toDomain($row);
            $out[$item->catalogItemId()] = $item;
        }

        return $out;
    }

    public function upsert(BranchMenuItem $item): BranchMenuItem
    {
        $row = CMP_BranchMenuItem::query()->updateOrCreate(
            [
                'branch_id' => $item->branchId(),
                'catalog_item_id' => $item->catalogItemId(),
            ],
            [
                'is_available' => $item->isAvailable(),
                'price_kopecks' => $item->priceKopecks(),
            ],
        );

        return $this->mapper->toDomain($row);
    }

    public function upsertMany(int $branchId, array $items): void
    {
        DB::transaction(function () use ($branchId, $items): void {
            foreach ($items as $item) {
                if (! $item instanceof BranchMenuItem) {
                    continue;
                }
                if ($item->branchId() !== $branchId) {
                    throw new \InvalidArgumentException('branch_id позиции меню не совпадает.');
                }
                $this->upsert($item);
            }
        });
    }

    public function delete(int $branchId, int $catalogItemId): void
    {
        CMP_BranchMenuItem::query()
            ->where('branch_id', $branchId)
            ->where('catalog_item_id', $catalogItemId)
            ->delete();
    }
}
