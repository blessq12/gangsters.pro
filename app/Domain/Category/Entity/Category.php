<?php

namespace App\Domain\Category\Entity;

use DateTimeImmutable;

final class Category
{
    private function __construct(
        private ?int $id,
        private string $name,
        private string $slug,
        private int $sortOrder,
        private bool $isActive,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        string $name,
        string $slug,
        int $sortOrder = 0,
        bool $isActive = true,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            name: $name,
            slug: $slug,
            sortOrder: $sortOrder,
            isActive: $isActive,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->touch();
    }

    public function changeSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
        $this->touch();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->touch();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

