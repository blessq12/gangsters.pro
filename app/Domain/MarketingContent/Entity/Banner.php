<?php

namespace App\Domain\MarketingContent\Entity;

/**
 * Публичный баннер главной (десктоп / мобила).
 */
final class Banner
{
    public function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly ?string $description,
        private readonly ?string $imageDesktop,
        private readonly ?string $imageMobile,
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

    public function description(): ?string
    {
        return $this->description;
    }

    public function imageDesktop(): ?string
    {
        return $this->imageDesktop;
    }

    public function imageMobile(): ?string
    {
        return $this->imageMobile;
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
