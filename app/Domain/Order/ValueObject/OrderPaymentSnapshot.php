<?php

namespace App\Domain\Order\ValueObject;

use App\Domain\Order\Enum\OrderPaymentMethod;

final readonly class OrderPaymentSnapshot
{
    public function __construct(
        private OrderPaymentMethod $method,
        private ?int $changeFromRubles,
    ) {}

    public function method(): OrderPaymentMethod
    {
        return $this->method;
    }

    public function changeFromRubles(): ?int
    {
        return $this->changeFromRubles;
    }
}
