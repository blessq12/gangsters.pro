<?php

namespace App\Domain\Checkout\Port;

/**
 * Товар набора дополнений из каталога.
 */
final readonly class CatalogComplementSetCandidate
{
    public function __construct(
        private int $productId,
        private string $productName,
        private int $priceRubles,
        private ?string $imageUrl,
    ) {}

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function priceRubles(): int
    {
        return $this->priceRubles;
    }

    public function imageUrl(): ?string
    {
        return $this->imageUrl;
    }
}
