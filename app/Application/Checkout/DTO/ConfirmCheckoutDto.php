<?php

namespace App\Application\Checkout\DTO;

final readonly class ConfirmCheckoutDto
{
    public function __construct(
        public string $checkoutId,
    ) {}
}
