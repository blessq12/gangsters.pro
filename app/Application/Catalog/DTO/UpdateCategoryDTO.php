<?php

namespace App\Application\Catalog\DTO;

final readonly class UpdateCategoryDTO
{
    public function __construct(
        public int $categoryId,
        public string $name,
        public int $sortOrder,
        public bool $isActive,
    ) {
    }
}
