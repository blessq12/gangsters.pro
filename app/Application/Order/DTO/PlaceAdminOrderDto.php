<?php

namespace App\Application\Order\DTO;

use DateTimeImmutable;

final readonly class PlaceAdminOrderDto
{
    /**
     * @param  list<array{product_id: int, qty: int}>  $lines
     */
    public function __construct(
        public string $clientName,
        public string $phone,
        public string $deliveryMethod,
        public string $address,
        public string $paymentMethod,
        public array $lines,
        public DateTimeImmutable $createdAt,
    ) {}
}
