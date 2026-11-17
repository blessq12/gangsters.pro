<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockWriteOff;
use App\Domain\Warehouse\Repository\StockWriteOffRepository;
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
        return WH_StockWriteOff::query()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (WH_StockWriteOff $row): StockWriteOff => $this->mapper->toDomain($row))
            ->all();
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
