<?php

namespace App\Domain\Order\ValueObject;

final readonly class OrderDeliveryAddress
{
    public function __construct(
        private string $street,
        private string $house,
        private ?string $entrance,
        private ?string $apartment,
    ) {}

    public function street(): string
    {
        return $this->street;
    }

    public function house(): string
    {
        return $this->house;
    }

    public function entrance(): ?string
    {
        return $this->entrance;
    }

    public function apartment(): ?string
    {
        return $this->apartment;
    }
}
