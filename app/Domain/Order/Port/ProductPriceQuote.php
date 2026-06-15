<?php

namespace App\Domain\Order\Port;

use App\Shared\ValueObject\Money;

final readonly class ProductPriceQuote
{
    public function __construct(
        private int $productId,
        private string $productName,
        private Money $unitPrice,
        private ?string $sku = null,
    ) {}

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function sku(): ?string
    {
        return $this->sku;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }
}
