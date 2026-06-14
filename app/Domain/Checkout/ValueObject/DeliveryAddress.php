<?php

namespace App\Domain\Checkout\ValueObject;

final readonly class DeliveryAddress
{
    public function __construct(
        private string $street,
        private string $house,
        private ?string $entrance = null,
        private ?string $apartment = null,
        private ?float $latitude = null,
        private ?float $longitude = null,
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

    public function latitude(): ?float
    {
        return $this->latitude;
    }

    public function longitude(): ?float
    {
        return $this->longitude;
    }
}
