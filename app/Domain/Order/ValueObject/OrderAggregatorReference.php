<?php

namespace App\Domain\Order\ValueObject;

final readonly class OrderAggregatorReference
{
    public function __construct(
        private string $partnerCode,
        private string $externalOrderId,
    ) {}

    public function partnerCode(): string
    {
        return $this->partnerCode;
    }

    public function externalOrderId(): string
    {
        return $this->externalOrderId;
    }
}
