<?php

namespace App\Domain\Order\ValueObjects;

class DeliveryInfo
{
    public function __construct(
        public readonly string $method,
        public readonly ?array $address,
        public readonly ?string $comment,
    ) {
    }
}

