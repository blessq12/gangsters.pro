<?php

namespace App\Domain\Supporting\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\StockLevel;

interface StockLevelRepository
{
    public function findByIngredientId(int $ingredientId): ?StockLevel;

    /**
     * @return list<StockLevel>
     */
    public function listAll(): array;

    /**
     * @return list<StockLevel>
     */
    public function listPage(int $offset, int $limit): array;

    public function countAll(): int;

    public function save(StockLevel $level): void;
}
