<?php

namespace App\Application\Operations\Order\DTO;

final readonly class UpdateAdminOrderDto
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     */
    public function __construct(
        public string $orderId,
        public array $items,
    ) {}
}
