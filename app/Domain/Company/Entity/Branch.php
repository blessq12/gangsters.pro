<?php

namespace App\Domain\Company\Entity;

/**
 * Филиал компании.
 */
final class Branch
{
    public function __construct(
        private readonly int $id,
        private readonly int $companyId,
        private readonly string $name,
        private readonly bool $isActive,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function companyId(): int
    {
        return $this->companyId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
