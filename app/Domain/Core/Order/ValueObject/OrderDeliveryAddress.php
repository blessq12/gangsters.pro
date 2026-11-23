<?php

namespace App\Domain\Core\Order\ValueObject;

/**
 * Адрес доставки в снимке заказа (для courier).
 */
final readonly class OrderDeliveryAddress
{
    public function __construct(
        private string $street,
        private string $house,
        private ?string $entrance = null,
        private ?string $floor = null,
        private ?string $apartment = null,
        private ?string $comment = null,
        private ?float $lat = null,
        private ?float $lng = null,
    ) {
        if (trim($this->street) === '' || trim($this->house) === '') {
            throw new \InvalidArgumentException('Улица и дом обязательны для доставки.');
        }

        if (($this->lat === null) !== ($this->lng === null)) {
            throw new \InvalidArgumentException('Координаты адреса должны быть парой lat/lng.');
        }

        if ($this->lat !== null && (! is_finite($this->lat) || ! is_finite((float) $this->lng))) {
            throw new \InvalidArgumentException('Некорректные координаты адреса.');
        }
    }

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

    public function floor(): ?string
    {
        return $this->floor;
    }

    public function apartment(): ?string
    {
        return $this->apartment;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function lat(): ?float
    {
        return $this->lat;
    }

    public function lng(): ?float
    {
        return $this->lng;
    }
}
