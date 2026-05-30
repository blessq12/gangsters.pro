<?php

namespace App\Application\Catalog\DTO;

final readonly class CreateCategoryDTO
{
    public function __construct(
        public string $name,
        public int $sortOrder = 0,
        public bool $isActive = true,
    ) {
    }
}
