<?php

namespace App\Domain\Checkout\Port;

use App\Shared\ValueObject\Money;

final readonly class ProductPriceQuote
{
    public function __construct(
        private int $productId,
        private string $productName,
        private Money $unitPrice,
    ) {}

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }
}
