<?php

namespace App\Application\Payment\DTO;

final readonly class InitiatePaymentDto
{
    public function __construct(
        public int $orderId,
        public string $method,
        public ?int $changeFromRubles,
        public int $amountKopecks,
    ) {}
}
