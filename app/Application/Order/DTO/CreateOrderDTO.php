<?php

namespace App\Application\Order\DTO;

final class CreateOrderDTO
{
    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @param array<string, mixed>|null $deliveryAddress
     */
    public function __construct(
        public readonly array $items,
        public readonly string $deliveryMethod,
        public readonly ?array $deliveryAddress,
        public readonly ?string $deliveryComment,
        public readonly string $paymentMethod,
        public readonly ?string $guestCustomerName = null,
        public readonly ?string $guestCustomerPhone = null,
        public readonly ?string $guestCustomerEmail = null,
    ) {
    }
}
