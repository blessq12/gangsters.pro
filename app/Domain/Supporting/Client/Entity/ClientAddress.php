<?php

namespace App\Domain\Supporting\Client\Entity;

/** Адрес как часть агрегата Client. */
final class ClientAddress
{
    private function __construct(
        private readonly string $id,
        private ?string $type,
        private ?string $title,
        private string $street,
        private string $house,
        private ?string $entrance,
        private ?string $floor,
        private ?string $apartment,
        private ?string $comment,
        private bool $isDefault,
        private ?float $lat = null,
        private ?float $lng = null,
    ) {}

    public static function create(
        ?string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $floor,
        ?string $apartment,
        ?string $comment,
        bool $makeDefault,
        ?float $lat = null,
        ?float $lng = null,
    ): self {
        $street = trim($street);
        $house = trim($house);

        if ($street === '' || $house === '') {
            throw new \InvalidArgumentException('Улица и дом обязательны.');
        }

        self::assertCoordsPair($lat, $lng);

        return new self(
            id: bin2hex(random_bytes(8)),
            type: self::normalizeOptional($type),
            title: self::normalizeOptional($title),
            street: $street,
            house: $house,
            entrance: self::normalizeOptional($entrance),
            floor: self::normalizeOptional($floor),
            apartment: self::normalizeOptional($apartment),
            comment: self::normalizeOptional($comment),
            isDefault: $makeDefault,
            lat: $lat,
            lng: $lng,
        );
    }

    public static function restore(
        string $id,
        ?string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $floor,
        ?string $apartment,
        ?string $comment,
        bool $isDefault,
        ?float $lat = null,
        ?float $lng = null,
    ): self {
        if ($id === '') {
            throw new \InvalidArgumentException('id адреса обязателен.');
        }

        self::assertCoordsPair($lat, $lng);

        return new self(
            id: $id,
            type: $type,
            title: $title,
            street: $street,
            house: $house,
            entrance: $entrance,
            floor: $floor,
            apartment: $apartment,
            comment: $comment,
            isDefault: $isDefault,
            lat: $lat,
            lng: $lng,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function title(): ?string
    {
        return $this->title;
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

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function lat(): ?float
    {
        return $this->lat;
    }

    public function lng(): ?float
    {
        return $this->lng;
    }

    public function update(
        ?string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $floor,
        ?string $apartment,
        ?string $comment,
        ?float $lat = null,
        ?float $lng = null,
    ): void {
        $street = trim($street);
        $house = trim($house);

        if ($street === '' || $house === '') {
            throw new \InvalidArgumentException('Улица и дом обязательны.');
        }

        self::assertCoordsPair($lat, $lng);

        $this->type = self::normalizeOptional($type);
        $this->title = self::normalizeOptional($title);
        $this->street = $street;
        $this->house = $house;
        $this->entrance = self::normalizeOptional($entrance);
        $this->floor = self::normalizeOptional($floor);
        $this->apartment = self::normalizeOptional($apartment);
        $this->comment = self::normalizeOptional($comment);
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function markDefault(): void
    {
        $this->isDefault = true;
    }

    public function markNotDefault(): void
    {
        $this->isDefault = false;
    }

    private static function assertCoordsPair(?float $lat, ?float $lng): void
    {
        if (($lat === null) !== ($lng === null)) {
            throw new \InvalidArgumentException('Координаты адреса должны быть парой lat/lng.');
        }

        if ($lat !== null && (! is_finite($lat) || ! is_finite((float) $lng))) {
            throw new \InvalidArgumentException('Некорректные координаты адреса.');
        }
    }

    private static function normalizeOptional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
