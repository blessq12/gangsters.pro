<?php

namespace App\Domain\Content\Entity;

/**
 * Публичный legal-документ компании.
 */
final class CompanyDocument
{
    public function __construct(
        private readonly int $id,
        private readonly string $slug,
        private readonly string $name,
        private readonly ?string $content,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function content(): ?string
    {
        return $this->content;
    }
}
