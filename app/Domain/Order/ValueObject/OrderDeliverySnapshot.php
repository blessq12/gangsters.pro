<?php

namespace App\Domain\Order\ValueObject;

use App\Domain\Order\Enum\OrderDeliveryMethod;

final readonly class OrderDeliverySnapshot
{
    public function __construct(
        private OrderDeliveryMethod $method,
        private ?OrderDeliveryAddress $address,
        private ?string $comment,
        private ?string $scheduledAt,
    ) {}

    public function method(): OrderDeliveryMethod
    {
        return $this->method;
    }

    public function address(): ?OrderDeliveryAddress
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
