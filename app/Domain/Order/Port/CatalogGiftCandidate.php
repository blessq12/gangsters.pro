<?php

namespace App\Domain\Order\Port;

/**
 * Товар-кандидат на подарок из каталога.
 */
final readonly class CatalogGiftCandidate
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
