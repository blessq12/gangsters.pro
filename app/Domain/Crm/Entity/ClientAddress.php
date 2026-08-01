<?php

namespace App\Domain\Crm\Entity;

/** Адрес как часть агрегата Client (JSON в таблице клиента). */
final class ClientAddress
{
    private function __construct(
        private readonly string $id,
        private ?string $type,
        private ?string $title,
        private string $street,
        private string $house,
        private ?string $entrance,
        private ?string $apartment,
        private ?string $comment,
        private bool $isDefault,
    ) {}

    public static function create(
        ?string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $apartment,
        ?string $comment,
        bool $makeDefault,
    ): self {
        $street = trim($street);
        $house = trim($house);

        if ($street === '' || $house === '') {
            throw new \InvalidArgumentException('Улица и дом обязательны.');
        }

        return new self(
            id: bin2hex(random_bytes(8)),
            type: self::normalizeOptional($type),
            title: self::normalizeOptional($title),
            street: $street,
            house: $house,
            entrance: self::normalizeOptional($entrance),
            apartment: self::normalizeOptional($apartment),
            comment: self::normalizeOptional($comment),
            isDefault: $makeDefault,
        );
    }

    public static function restore(
        string $id,
        ?string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $apartment,
        ?string $comment,
        bool $isDefault,
    ): self {
        if ($id === '') {
            throw new \InvalidArgumentException('id адреса обязателен.');
        }

        return new self(
            id: $id,
            type: $type,
            title: $title,
            street: $street,
            house: $house,
            entrance: $entrance,
            apartment: $apartment,
            comment: $comment,
            isDefault: $isDefault,
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

    public function update(
        ?string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $apartment,
        ?string $comment,
    ): void {
        $street = trim($street);
        $house = trim($house);

        if ($street === '' || $house === '') {
            throw new \InvalidArgumentException('Улица и дом обязательны.');
        }

        $this->type = self::normalizeOptional($type);
        $this->title = self::normalizeOptional($title);
        $this->street = $street;
        $this->house = $house;
        $this->entrance = self::normalizeOptional($entrance);
        $this->apartment = self::normalizeOptional($apartment);
        $this->comment = self::normalizeOptional($comment);
    }

    public function markDefault(): void
    {
        $this->isDefault = true;
    }

    public function markNotDefault(): void
    {
        $this->isDefault = false;
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
