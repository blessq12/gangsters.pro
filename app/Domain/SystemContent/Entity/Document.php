<?php

namespace App\Domain\SystemContent\Entity;

final class Document
{
    public function __construct(
        private readonly int $id,
        private readonly string $key,
        private readonly string $title,
        private readonly ?string $content,
        private readonly bool $isActive,
    ) {
    }

    public function id(): int { return $this->id; }
    public function key(): string { return $this->key; }
    public function title(): string { return $this->title; }
    public function content(): ?string { return $this->content; }
    public function isActive(): bool { return $this->isActive; }
}

