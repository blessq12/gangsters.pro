<?php

namespace App\Domain\Client\Entity;

use App\Domain\Client\ValueObject\ClientAddressId;

final class ClientAddress
{
    private function __construct(
        private readonly ?ClientAddressId $id,
        private readonly ?string $type,
        private readonly ?string $title,
        private readonly string $street,
        private readonly string $house,
        private readonly ?string $entrance,
        private readonly ?string $apartment,
        private readonly ?string $comment,
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
        return new self(
            id: null,
            type: $type,
            title: $title,
            street: $street,
            house: $house,
            entrance: $entrance,
            apartment: $apartment,
            comment: $comment,
            isDefault: $makeDefault,
        );
    }

    public static function restore(
        ClientAddressId $id,
        ?string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $apartment,
        ?string $comment,
        bool $isDefault,
    ): self {
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

    public function assignId(ClientAddressId $id): self
    {
        return new self(
            id: $id,
            type: $this->type,
            title: $this->title,
            street: $this->street,
            house: $this->house,
            entrance: $this->entrance,
            apartment: $this->apartment,
            comment: $this->comment,
            isDefault: $this->isDefault,
        );
    }

    public function id(): ?ClientAddressId
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

    public function markDefault(): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            title: $this->title,
            street: $this->street,
            house: $this->house,
            entrance: $this->entrance,
            apartment: $this->apartment,
            comment: $this->comment,
            isDefault: true,
        );
    }

    public function markNotDefault(): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            title: $this->title,
            street: $this->street,
            house: $this->house,
            entrance: $this->entrance,
            apartment: $this->apartment,
            comment: $this->comment,
            isDefault: false,
        );
    }
}
