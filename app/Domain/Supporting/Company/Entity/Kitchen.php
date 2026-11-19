<?php

namespace App\Domain\Supporting\Company\Entity;

/**
 * Кухня всегда принадлежит одному Branch (branchId).
 * Адрес и координаты живут только в Company; другие BC хранят kitchenId.
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
    ) {
        if ($this->branchId < 1) {
            throw new \InvalidArgumentException('Kitchen всегда принадлежит Branch (branchId > 0).');
        }
    }

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
