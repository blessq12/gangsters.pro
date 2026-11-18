<?php

namespace App\Infrastructure\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\StockLevel;
use App\Infrastructure\Warehouse\Model\WH_StockLevel;

final class StockLevelMapper
{
    public function toDomain(WH_StockLevel $row): StockLevel
    {
        return new StockLevel(
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
            'ingredient_id' => $level->ingredientId(),
            'quantity' => $level->quantity(),
        ];
    }
}
