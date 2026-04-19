<?php

namespace App\Domain\Shopping\Entities;

final class CartLine
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly ?array $payload = null,
    ) {
    }

    /**
     * @return array{product_id: int, quantity: int}
     */
    public function toOrderItemRow(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
        ];
    }
}
