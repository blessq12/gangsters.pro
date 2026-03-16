<?php

namespace App\Domain\Client\Entity;

use DateTimeImmutable;

final class ClientAddress
{
    private function __construct(
        private ?int $id,
        private int $clientId,
        private string $type,
        private ?string $title,
        private string $street,
        private string $house,
        private ?string $entrance,
        private ?string $apartment,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        int $clientId,
        string $type,
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $apartment,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            clientId: $clientId,
            type: $type,
            title: $title,
            street: $street,
            house: $house,
            entrance: $entrance,
            apartment: $apartment,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    public function type(): string
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

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(
        ?string $title,
        string $street,
        string $house,
        ?string $entrance,
        ?string $apartment,
    ): void {
        $this->title = $title;
        $this->street = $street;
        $this->house = $house;
        $this->entrance = $entrance;
        $this->apartment = $apartment;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
