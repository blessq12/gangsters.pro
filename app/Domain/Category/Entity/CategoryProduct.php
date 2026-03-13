<?php

namespace App\Domain\Category\Entity;

final class CategoryProduct
{
    private function __construct(
        private ?int $id,
        private int $categoryId,
        private int $productId,
        private int $sortOrder,
    ) {
    }

    public static function link(
        int $categoryId,
        int $productId,
        int $sortOrder = 0,
    ): self {
        return new self(
            id: null,
            categoryId: $categoryId,
            productId: $productId,
            sortOrder: $sortOrder,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}

