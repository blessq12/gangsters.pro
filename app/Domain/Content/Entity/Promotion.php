<?php

namespace App\Domain\Content\Entity;

/**
 * Публичная акция витрины.
 */
final class Promotion
{
    public function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly ?string $body,
        private readonly ?string $image,
        private readonly int $sortOrder,
        private readonly bool $isActive,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function body(): ?string
    {
        return $this->body;
    }

    public function image(): ?string
    {
        return $this->image;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
