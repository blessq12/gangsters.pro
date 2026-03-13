<?php

namespace App\Domain\Product\VO;

final class ImageVariant
{
    public function __construct(
        private readonly string $size,  // thumb | medium | large | custom
        private readonly string $path,
        private readonly int $width,
        private readonly int $height,
    ) {
    }

    public function size(): string
    {
        return $this->size;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }
}

