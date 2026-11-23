<?php

namespace App\Domain\Supporting\Catalog\ValueObject;

/**
 * Строка техкарты: opaque ссылка на ингредиент склада + количество и единица.
 * Catalog не знает Warehouse — ingredient_ref без семантики чужого BC.
 */
final readonly class TechCardLine
{
    private string $ingredientRef;

    private string $unit;

    public function __construct(
        string $ingredientRef,
        private float $quantity,
        string $unit,
    ) {
        $ref = trim($ingredientRef);
        if ($ref === '') {
            throw new \InvalidArgumentException('ingredient_ref обязателен.');
        }
        if ($this->quantity <= 0) {
            throw new \InvalidArgumentException('quantity техкарты должно быть > 0.');
        }
        $normalizedUnit = trim($unit);
        if ($normalizedUnit === '') {
            throw new \InvalidArgumentException('unit техкарты обязателен.');
        }

        $this->ingredientRef = $ref;
        $this->unit = $normalizedUnit;
    }

    public function ingredientRef(): string
    {
        return $this->ingredientRef;
    }

    public function quantity(): float
    {
        return $this->quantity;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
