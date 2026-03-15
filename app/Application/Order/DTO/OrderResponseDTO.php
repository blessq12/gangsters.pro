<?php

namespace App\Application\Order\DTO;

final class OrderResponseDTO
{
    /**
     * @param array<string, mixed> $customer
     * @param array<int, array<string, mixed>> $items
     * @param array<string, mixed>|null $delivery
     * @param array<string, mixed>|null $payment
     */
    public function __construct(
        public readonly string $id,
        public readonly int $clientId,
        public readonly array $customer,
        public readonly string $status,
        public readonly int $subtotal,
        public readonly int $discountTotal,
        public readonly int $total,
        public readonly ?array $delivery,
        public readonly ?array $payment,
        public readonly array $items,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }
}
