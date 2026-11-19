<?php

namespace App\Domain\Supporting\Warehouse\Entity;

/** Остаток ингредиента на складе. */
final class StockLevel
{
    public function __construct(
        private readonly int $ingredientId,
        private float $quantity,
    ) {
        if ($ingredientId < 1) {
            throw new \InvalidArgumentException('ingredientId должен быть положительным.');
        }

        if ($quantity < 0) {
            throw new \InvalidArgumentException('Остаток не может быть отрицательным.');
        }
    }

    public function ingredientId(): int
    {
        return $this->ingredientId;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }
}
