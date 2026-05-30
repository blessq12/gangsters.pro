<?php

namespace App\Application\Operations\Order\DTO;

final readonly class CreateAdminOrderDTO
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @param  array<string, mixed>|null  $deliveryAddress
     */
    public function __construct(
        public ?int $clientId,
        public ?string $guestCustomerName,
        public ?string $guestCustomerPhone,
        public ?string $guestCustomerEmail,
        public array $items,
        public string $deliveryMethod,
        public ?array $deliveryAddress,
        public ?string $deliveryComment,
        public string $paymentMethod,
    ) {
    }
}
