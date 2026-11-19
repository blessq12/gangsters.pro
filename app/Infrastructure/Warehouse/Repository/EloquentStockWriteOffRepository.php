<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\StockWriteOff;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;
use App\Infrastructure\Warehouse\Mapper\StockWriteOffMapper;
use App\Infrastructure\Warehouse\Model\WH_StockWriteOff;

final class EloquentStockWriteOffRepository implements StockWriteOffRepository
{
    public function __construct(
        private readonly StockWriteOffMapper $mapper,
    ) {}

    public function findById(int $id): ?StockWriteOff
    {
        $row = WH_StockWriteOff::query()->find($id);

        return $row instanceof WH_StockWriteOff ? $this->mapper->toDomain($row) : null;
    }

    public function listAll(): array
    {
        return $this->listPage(0, max(1, $this->countAll()));
    }

    public function listPage(int $offset, int $limit): array
    {
        return WH_StockWriteOff::query()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (WH_StockWriteOff $row): StockWriteOff => $this->mapper->toDomain($row))
            ->all();
    }

    public function countAll(): int
    {
        return (int) WH_StockWriteOff::query()->count();
    }

    public function listByKitchenTicketId(int $kitchenTicketId): array
    {
        return WH_StockWriteOff::query()
            ->where('kitchen_ticket_id', $kitchenTicketId)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (WH_StockWriteOff $row): StockWriteOff => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(StockWriteOff $writeOff): void
    {
        if ($writeOff->hasId()) {
            WH_StockWriteOff::query()
                ->whereKey($writeOff->id())
                ->update($this->mapper->toPersistence($writeOff));

            return;
        }

        $row = WH_StockWriteOff::query()->create($this->mapper->toPersistence($writeOff));
        $writeOff->assignId((int) $row->id);
    }
}
