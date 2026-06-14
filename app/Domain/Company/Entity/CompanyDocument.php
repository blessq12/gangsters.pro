<?php

namespace App\Domain\Company\Entity;

/**
 * Публичный legal-документ компании.
 */
final class CompanyDocument
{
    public function __construct(
        private readonly int $id,
        private readonly string $key,
        private readonly string $title,
        private readonly ?string $content,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function content(): ?string
    {
        return $this->content;
    }
}
