<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\StockWriteOff;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;
use App\Infrastructure\Warehouse\Mapper\StockWriteOffMapper;
use App\Infrastructure\Warehouse\Model\StockWriteOffModel;

final class EloquentStockWriteOffRepository implements StockWriteOffRepository
{
    public function __construct(
        private readonly StockWriteOffMapper $mapper,
    ) {}

    public function findById(int $id): ?StockWriteOff
    {
        $row = StockWriteOffModel::query()->find($id);

        return $row instanceof StockWriteOffModel ? $this->mapper->toDomain($row) : null;
    }

    public function listByBranch(int $branchId): array
    {
        return $this->listByBranchPage($branchId, 0, max(1, $this->countByBranch($branchId)));
    }

    public function listByBranchPage(int $branchId, int $offset, int $limit): array
    {
        return StockWriteOffModel::query()
            ->where('branch_id', $branchId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (StockWriteOffModel $row): StockWriteOff => $this->mapper->toDomain($row))
            ->all();
    }

    public function countByBranch(int $branchId): int
    {
        return (int) StockWriteOffModel::query()->where('branch_id', $branchId)->count();
    }

    public function listByKitchenTicketId(int $kitchenTicketId): array
    {
        return StockWriteOffModel::query()
            ->where('kitchen_ticket_id', $kitchenTicketId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (StockWriteOffModel $row): StockWriteOff => $this->mapper->toDomain($row))
            ->all();
    }

    public function listByBranchAndIngredient(int $branchId, int $ingredientId): array
    {
        return StockWriteOffModel::query()
            ->where('branch_id', $branchId)
            ->where('ingredient_id', $ingredientId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (StockWriteOffModel $row): StockWriteOff => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(StockWriteOff $writeOff): void
    {
        if ($writeOff->hasId()) {
            StockWriteOffModel::query()
                ->whereKey($writeOff->id())
                ->update($this->mapper->toPersistence($writeOff));

            return;
        }

        $row = StockWriteOffModel::query()->create($this->mapper->toPersistence($writeOff));
        $writeOff->assignId((int) $row->id);
    }
}
