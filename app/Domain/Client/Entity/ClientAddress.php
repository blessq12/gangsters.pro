<?php

namespace App\Domain\Client\Entity;

use DateTimeImmutable;

final class ClientAddress
{
    private function __construct(
        private ?int $id,
        private int $clientId,
        private string $type, // default | additional
        private ?string $title,
        private string $street,
        private string $house,
        private ?string $liter,
        private ?string $staircase,
        private ?string $apartment,
        private ?string $entranceCode,
        private ?string $floor,
        private ?string $comment,
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
        ?string $liter,
        ?string $staircase,
        ?string $apartment,
        ?string $entranceCode,
        ?string $floor,
        ?string $comment,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            clientId: $clientId,
            type: $type,
            title: $title,
            street: $street,
            house: $house,
            liter: $liter,
            staircase: $staircase,
            apartment: $apartment,
            entranceCode: $entranceCode,
            floor: $floor,
            comment: $comment,
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

    public function liter(): ?string
    {
        return $this->liter;
    }

    public function staircase(): ?string
    {
        return $this->staircase;
    }

    public function apartment(): ?string
    {
        return $this->apartment;
    }

    public function entranceCode(): ?string
    {
        return $this->entranceCode;
    }

    public function floor(): ?string
    {
        return $this->floor;
    }

    public function comment(): ?string
    {
        return $this->comment;
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
        ?string $liter,
        ?string $staircase,
        ?string $apartment,
        ?string $entranceCode,
        ?string $floor,
        ?string $comment,
    ): void {
        $this->title = $title;
        $this->street = $street;
        $this->house = $house;
        $this->liter = $liter;
        $this->staircase = $staircase;
        $this->apartment = $apartment;
        $this->entranceCode = $entranceCode;
        $this->floor = $floor;
        $this->comment = $comment;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

