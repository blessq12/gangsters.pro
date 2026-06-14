<?php

namespace App\Domain\Catalog\ValueObject;

/**
 * Пищевая ценность товара.
 */
final class Nutrition
{
    public function __construct(
        private readonly float $calories,
        private readonly float $proteins,
        private readonly float $fats,
        private readonly float $carbs,
        private readonly string $basis = 'per_100g',
    ) {}

    public function calories(): float
    {
        return $this->calories;
    }

    public function proteins(): float
    {
        return $this->proteins;
    }

    public function fats(): float
    {
        return $this->fats;
    }

    public function carbs(): float
    {
        return $this->carbs;
    }

    public function basis(): string
    {
        return $this->basis;
    }
}
