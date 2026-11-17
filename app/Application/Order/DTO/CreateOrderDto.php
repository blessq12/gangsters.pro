<?php

namespace App\Application\Order\DTO;

final readonly class CreateOrderDto
{
    /**
     * @param  array<string, mixed>  $quoteSnapshot
     */
    public function __construct(
        public string $clientRequestId,
        public array $quoteSnapshot,
    ) {}
}
