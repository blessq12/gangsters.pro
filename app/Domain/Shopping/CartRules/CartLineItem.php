<?php

namespace App\Domain\Shopping\CartRules;

/**
 * Строка корзины в снимке правил (пользовательская или системная).
 */
final readonly class CartLineItem
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public CartLineOrigin $origin,
        public string $lineKey,
        public ?int $finalUnitPriceKopecks = null,
    ) {}

    public function withFinalUnitPriceKopecks(?int $kopecks): self
    {
        return new self(
            $this->productId,
            $this->quantity,
            $this->origin,
            $this->lineKey,
            $kopecks,
        );
    }
}
