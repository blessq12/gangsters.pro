<?php

namespace App\Application\Checkout\DTO;

final readonly class UpdateCheckoutCartDto
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function __construct(
        public string $checkoutId,
        public int $productId,
        public int $quantity,
        public ?array $payload = null,
    ) {}
}
