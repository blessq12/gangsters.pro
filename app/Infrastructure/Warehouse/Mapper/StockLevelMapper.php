<?php

namespace App\Infrastructure\Warehouse\Mapper;

use App\Domain\Supporting\Warehouse\Entity\StockLevel;
use App\Infrastructure\Warehouse\Model\StockLevelModel;

final class StockLevelMapper
{
    public function toDomain(StockLevelModel $row): StockLevel
    {
        return new StockLevel(
            branchId: (int) $row->branch_id,
            ingredientId: (int) $row->ingredient_id,
            quantity: (float) $row->quantity,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(StockLevel $level): array
    {
        return [
            'branch_id' => $level->branchId(),
            'ingredient_id' => $level->ingredientId(),
            'quantity' => $level->quantity(),
        ];
    }
}
