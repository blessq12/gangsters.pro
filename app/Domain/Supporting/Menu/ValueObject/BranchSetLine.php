<?php

namespace App\Domain\Supporting\Menu\ValueObject;

final class BranchSetLine
{
    public function __construct(
        private readonly int $catalogItemId,
        private readonly int $quantity,
    ) {
        if ($this->catalogItemId < 1) {
            throw new \InvalidArgumentException('catalog_item_id должен быть положительным.');
        }
        if ($this->quantity < 1) {
            throw new \InvalidArgumentException('quantity должен быть ≥ 1.');
        }
    }

    public function catalogItemId(): int
    {
        return $this->catalogItemId;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
