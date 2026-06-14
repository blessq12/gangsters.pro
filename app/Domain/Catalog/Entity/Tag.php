<?php

namespace App\Domain\Catalog\Entity;

/**
 * Тег витрины — справочник меток товаров.
 */
final class Tag
{
    public function __construct(
        private readonly int $id,
        private readonly string $code,
        private readonly string $label,
        private readonly string $color,
        private readonly bool $isActive,
        private readonly int $sortOrder,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function color(): string
    {
        return $this->color;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
