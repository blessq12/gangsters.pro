<?php

namespace App\Domain\Order\OrderDraft\ValueObject;

use App\Shared\Enum\DeliveryMethod;

final readonly class DeliverySnapshot
{
    public function __construct(
        private DeliveryMethod $method,
        private ?DeliveryAddress $address,
        private ?string $comment,
        private ?string $scheduledAt,
    ) {}

    public function method(): DeliveryMethod
    {
        return $this->method;
    }

    public function address(): ?DeliveryAddress
    {
        return $this->address;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function scheduledAt(): ?string
    {
        return $this->scheduledAt;
    }
}
