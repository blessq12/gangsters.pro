<?php

namespace App\Domain\Content\ValueObject;

/**
 * Адрес кухни / базы доставки.
 */
final readonly class KitchenAddress
{
    public function __construct(
        private ?string $city,
        private ?string $street,
        private ?string $house,
        private ?string $comment,
        private ?string $searchLine,
    ) {}

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

    public function searchLine(): ?string
    {
        return $this->searchLine;
    }
}
