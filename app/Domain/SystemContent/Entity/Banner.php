<?php

namespace App\Domain\SystemContent\Entity;

final class Banner
{
    public function __construct(
        private readonly int $id,
        private readonly ?string $title,
        private readonly ?string $description,
        private readonly ?string $imagePath,
        private readonly ?string $imageMobilePath = null,
        private readonly ?string $imageDesktopPath = null,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function imagePath(): ?string
    {
        return $this->imagePath;
    }

    public function imageMobilePath(): ?string
    {
        return $this->imageMobilePath;
    }

    public function imageDesktopPath(): ?string
    {
        return $this->imageDesktopPath;
    }
}

