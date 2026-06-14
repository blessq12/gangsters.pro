<?php

namespace App\Domain\Order\ValueObject;

use App\Shared\Enum\PaymentMethod;

final readonly class OrderPaymentSnapshot
{
    public function __construct(
        private PaymentMethod $method,
        private ?int $changeFromRubles,
    ) {}

    public function method(): PaymentMethod
    {
        return $this->method;
    }

    public function changeFromRubles(): ?int
    {
        return $this->changeFromRubles;
    }
}
