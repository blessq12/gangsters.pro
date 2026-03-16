<?php

namespace App\Domain\Order\ValueObjects;

class PaymentInfo
{
    public function __construct(
        public readonly string $method,
        public readonly ?string $status,
    ) {
    }
}

