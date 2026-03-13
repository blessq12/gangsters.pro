<?php

namespace App\Domain\Product\Entity;

use App\Domain\Product\VO\ImageVariant;

final class ProductImage
{
    /**
     * @param ImageVariant[] $variants
     */
    private function __construct(
        private ?int $id,
        private int $sortOrder,
        private array $variants,
    ) {
    }

    /**
     * @param ImageVariant[] $variants
     */
    public static function create(
        array $variants,
        int $sortOrder = 0,
    ): self {
        return new self(
            id: null,
            sortOrder: $sortOrder,
            variants: $variants,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @return ImageVariant[]
     */
    public function variants(): array
    {
        return $this->variants;
    }
}

