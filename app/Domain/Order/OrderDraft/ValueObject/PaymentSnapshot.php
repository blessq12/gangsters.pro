<?php

namespace App\Domain\Order\OrderDraft\ValueObject;

use App\Shared\Enum\PaymentMethod;

final readonly class PaymentSnapshot
{
    public function __construct(
        private PaymentMethod $method,
        private ?int $changeFromRubles = null,
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
