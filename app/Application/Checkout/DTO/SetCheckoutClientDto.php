<?php

namespace App\Application\Checkout\DTO;

final readonly class SetCheckoutClientDto
{
    public function __construct(
        public string $checkoutId,
        public ?int $clientId = null,
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}
}
