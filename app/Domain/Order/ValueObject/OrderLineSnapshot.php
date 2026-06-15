<?php

namespace App\Domain\Order\ValueObject;

use App\Shared\ValueObject\Money;

final readonly class OrderLineSnapshot
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

    public function sku(): ?string
    {
        return $this->sku;
    }

    public function lineKind(): string
    {
        if (! is_array($this->payload)) {
            return 'user';
        }

        $kind = $this->payload['kind'] ?? null;

        return is_string($kind) && $kind !== '' ? $kind : 'user';
    }

    public function isPromotionBenefitLine(): bool
    {
        return in_array($this->lineKind(), ['gift', 'complement'], true);
    }

    public function lineTotal(): Money
    {
        return Money::rubles($this->unitPrice->amountRubles() * $this->quantity);
    }
}
