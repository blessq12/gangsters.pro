<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockLevel;

interface StockLevelRepository
{
    public function findByIngredientId(int $ingredientId): ?StockLevel;

    /**
     * @return list<StockLevel>
     */
    public function listAll(): array;

    public function save(StockLevel $level): void;
}
