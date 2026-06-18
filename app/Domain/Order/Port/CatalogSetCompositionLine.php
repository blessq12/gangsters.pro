<?php

namespace App\Domain\Order\Port;

/**
 * Строка состава набора для ACL Order → Catalog.
 */
final readonly class CatalogSetCompositionLine
{
    public function __construct(
        private int $productId,
        private int $quantity,
    ) {
        if ($productId < 1) {
            throw new \InvalidArgumentException('Идентификатор товара состава набора должен быть положительным.');
        }

        if ($quantity < 1) {
            throw new \InvalidArgumentException('Количество товара в составе набора должно быть не меньше 1.');
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
