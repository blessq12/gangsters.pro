<?php

namespace App\Application\Warehouse\DTO;

final readonly class AdjustStockLevelDto
{
    public function __construct(
        public int $ingredientId,
        public float $delta,
    ) {}
}
