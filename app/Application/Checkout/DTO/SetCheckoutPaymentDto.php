<?php

namespace App\Application\Checkout\DTO;

use App\Shared\Enum\PaymentMethod;

final readonly class SetCheckoutPaymentDto
{
    public function __construct(
        public string $checkoutId,
        public PaymentMethod $method,
        public ?int $changeFromRubles = null,
    ) {}
}
