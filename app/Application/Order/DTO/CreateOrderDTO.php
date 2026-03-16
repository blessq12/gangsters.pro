<?php

namespace App\Application\Order\DTO;

final class CreateOrderDTO
{
    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @param array<string, mixed>|null $deliveryAddress
     */
    public function __construct(
        public readonly ?int $clientId,
        public readonly array $items,
        public readonly string $deliveryMethod,
        public readonly ?array $deliveryAddress,
        public readonly ?string $deliveryComment,
        public readonly string $paymentMethod,
    ) {
    }
}
