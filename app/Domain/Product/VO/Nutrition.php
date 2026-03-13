<?php

namespace App\Domain\Product\VO;

final class Nutrition
{
    public function __construct(
        private readonly float $calories, // ккал
        private readonly float $proteins, // г
        private readonly float $fats,     // г
        private readonly float $carbs,    // г
        private readonly string $basis = 'per_100g', // per_100g | per_portion
    ) {
    }

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

