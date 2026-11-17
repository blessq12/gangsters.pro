<?php

namespace App\Domain\Company\Entity;

/**
 * Кухня филиала: адрес и координаты. Зоны доставки сюда не входят — ими владеет Delivery.
 */
final class Kitchen
{
    public function __construct(
        private readonly int $id,
        private readonly int $branchId,
        private readonly ?string $city,
        private readonly ?string $street,
        private readonly ?string $house,
        private readonly ?string $comment,
        private readonly ?float $latitude,
        private readonly ?float $longitude,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function street(): ?string
    {
        return $this->street;
    }

    public function house(): ?string
    {
        return $this->house;
    }

    public function comment(): ?string
    {
        return $this->comment;
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
