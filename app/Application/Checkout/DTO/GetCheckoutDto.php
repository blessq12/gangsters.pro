<?php

namespace App\Application\Checkout\DTO;

final readonly class GetCheckoutDto
{
    public function __construct(
        public string $checkoutId,
    ) {}
}
