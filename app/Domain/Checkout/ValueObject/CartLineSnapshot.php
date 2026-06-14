<?php

namespace App\Domain\Checkout\ValueObject;

use App\Domain\Checkout\Port\ProductPriceQuote;
use App\Shared\ValueObject\Money;

final readonly class CartLineSnapshot
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function __construct(
        private int $productId,
        private string $productName,
        private int $quantity,
        private Money $unitPrice,
        private ?array $payload = null,
    ) {
        if ($this->quantity < 1) {
            throw new \InvalidArgumentException('Количество позиции корзины должно быть больше нуля.');
        }
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    public static function fromQuote(ProductPriceQuote $quote, int $quantity, ?array $payload = null): self
    {
        return new self(
            productId: $quote->productId(),
            productName: $quote->productName(),
            quantity: $quantity,
            unitPrice: $quote->unitPrice(),
            payload: $payload,
        );
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function payload(): ?array
    {
        return $this->payload;
    }

    public function lineKind(): string
    {
        if (! is_array($this->payload)) {
            return 'user';
        }

        $kind = $this->payload['kind'] ?? null;

        return is_string($kind) && $kind !== '' ? $kind : 'user';
    }

    public function matchesIdentity(int $productId, string $lineKind): bool
    {
        return $this->productId() === $productId && $this->lineKind() === $lineKind;
    }

    public function lineTotal(): Money
    {
        return Money::rubles($this->unitPrice->amountRubles() * $this->quantity);
    }
}
