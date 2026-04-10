<?php

namespace App\Domain\Order\ValueObjects;

class ProductSnapshot
{
    /**
     * @param  int  $listPrice  Копейки (RUB)
     * @param  int  $finalPrice  Копейки (RUB)
     */
    public function __construct(
        public readonly string $name,
        public readonly string $sku,
        public readonly int $listPrice,
        public readonly int $finalPrice,
        public readonly array $attributes = [],
        public readonly array $media = [],
    ) {}
}
