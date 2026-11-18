<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockLevel;
use App\Domain\Warehouse\Repository\StockLevelRepository;
use App\Infrastructure\Warehouse\Mapper\StockLevelMapper;
use App\Infrastructure\Warehouse\Model\WH_StockLevel;

final class EloquentStockLevelRepository implements StockLevelRepository
{
    public function __construct(
        private readonly StockLevelMapper $mapper,
    ) {}

    public function findByIngredientId(int $ingredientId): ?StockLevel
    {
        $row = WH_StockLevel::query()
            ->where('ingredient_id', $ingredientId)
            ->first();

        return $row instanceof WH_StockLevel ? $this->mapper->toDomain($row) : null;
    }

    public function listAll(): array
    {
        return WH_StockLevel::query()
            ->orderBy('ingredient_id')
            ->get()
            ->map(fn (WH_StockLevel $row): StockLevel => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(StockLevel $level): void
    {
        WH_StockLevel::query()->updateOrCreate(
            ['ingredient_id' => $level->ingredientId()],
            $this->mapper->toPersistence($level),
        );
    }
}
