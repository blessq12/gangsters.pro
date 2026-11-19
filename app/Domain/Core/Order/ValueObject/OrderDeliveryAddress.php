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
    ) {
        if (trim($this->street) === '' || trim($this->house) === '') {
            throw new \InvalidArgumentException('Улица и дом обязательны для доставки.');
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
}
