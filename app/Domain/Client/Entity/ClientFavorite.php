<?php

namespace App\Domain\Client\Entity;

final class ClientFavorite
{
    private function __construct(
        private readonly int $productId,
        private readonly ?string $productName,
        private readonly ?float $priceRub,
        private readonly ?string $weight,
    ) {}

    public static function create(
        int $productId,
        ?string $productName,
        ?float $priceRub,
        ?string $weight,
    ): self {
        if ($productId <= 0) {
            throw new \InvalidArgumentException('Идентификатор товара должен быть положительным.');
        }

        return new self(
            productId: $productId,
            productName: $productName,
            priceRub: $priceRub,
            weight: $weight,
        );
    }

    public static function restore(
        int $productId,
        ?string $productName,
        ?float $priceRub,
        ?string $weight,
    ): self {
        return new self(
            productId: $productId,
            productName: $productName,
            priceRub: $priceRub,
            weight: $weight,
        );
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): ?string
    {
        return $this->productName;
    }

    public function priceRub(): ?float
    {
        return $this->priceRub;
    }

    public function weight(): ?string
    {
        return $this->weight;
    }
}
