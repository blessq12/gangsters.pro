<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\StockLevel;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;
use App\Infrastructure\Warehouse\Mapper\StockLevelMapper;
use App\Infrastructure\Warehouse\Model\WH_StockLevel;

final class EloquentStockLevelRepository implements StockLevelRepository
{
    public function __construct(
        private readonly StockLevelMapper $mapper,
    ) {}

    public function findByBranchAndIngredient(int $branchId, int $ingredientId): ?StockLevel
    {
        $row = WH_StockLevel::query()
            ->where('branch_id', $branchId)
            ->where('ingredient_id', $ingredientId)
            ->first();

        return $row instanceof WH_StockLevel ? $this->mapper->toDomain($row) : null;
    }

    public function listByBranch(int $branchId): array
    {
        return $this->listByBranchPage($branchId, 0, max(1, $this->countByBranch($branchId)));
    }

    public function listByBranchPage(int $branchId, int $offset, int $limit): array
    {
        return WH_StockLevel::query()
            ->where('branch_id', $branchId)
            ->orderBy('ingredient_id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (WH_StockLevel $row): StockLevel => $this->mapper->toDomain($row))
            ->all();
    }

    public function countByBranch(int $branchId): int
    {
        return (int) WH_StockLevel::query()->where('branch_id', $branchId)->count();
    }

    public function save(StockLevel $level): void
    {
        WH_StockLevel::query()->updateOrCreate(
            [
                'branch_id' => $level->branchId(),
                'ingredient_id' => $level->ingredientId(),
            ],
            $this->mapper->toPersistence($level),
        );
    }
}
