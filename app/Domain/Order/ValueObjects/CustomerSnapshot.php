<?php

namespace App\Domain\Order\ValueObjects;

class CustomerSnapshot
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
        public readonly ?string $email,
        public readonly ?array $address,
    ) {
    }
}

