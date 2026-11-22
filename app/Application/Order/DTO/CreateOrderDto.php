<?php

namespace App\Application\Order\DTO;

final readonly class CreateOrderDto
{
    public function __construct(
        public string $clientRequestId,
        public QuoteOrderDto $checkout,
    ) {}
}
