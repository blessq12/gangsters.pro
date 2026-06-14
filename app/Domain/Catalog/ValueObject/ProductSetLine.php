<?php

namespace App\Domain\Catalog\ValueObject;

/**
 * Строка состава набора: товар и его количество внутри набора.
 */
final class ProductSetLine
{
    public function __construct(
        private readonly int $productId,
        private readonly int $quantity,
    ) {
        if ($productId < 1) {
            throw new \InvalidArgumentException('Идентификатор товара набора должен быть положительным.');
        }

        if ($quantity < 1) {
            throw new \InvalidArgumentException('Количество товара в наборе должно быть не меньше 1.');
        }
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
