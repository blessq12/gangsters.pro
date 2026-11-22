<?php

namespace App\Domain\Supporting\Company\Entity;

final class BranchCategory
{
    public function __construct(
        private readonly int $id,
        private readonly int $branchId,
        private readonly string $name,
        private readonly string $slug,
        private readonly int $sortOrder,
        private readonly bool $isActive,
        private readonly bool $isAccompanying = false,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function branchId(): int
    {
        return $this->branchId;
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

    public function isAccompanying(): bool
    {
        return $this->isAccompanying;
    }
}
