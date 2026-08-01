<?php

namespace App\Application\Order\DTO;

use DateTimeImmutable;

final readonly class CreateOrderDto
{
    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $client
     * @param  array<string, mixed>  $delivery
     * @param  array<string, mixed>  $payment
     */
    public function __construct(
        public string $clientRequestId,
        public array $cart,
        public array $client,
        public array $delivery,
        public array $payment,
        public DateTimeImmutable $createdAt,
    ) {}
}
