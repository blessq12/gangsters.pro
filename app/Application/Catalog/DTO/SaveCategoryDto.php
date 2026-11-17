<?php

namespace App\Application\Catalog\DTO;

final readonly class SaveCategoryDto
{
    public function __construct(
        public ?int $id,
        public string $name,
        public bool $isActive,
        public bool $isAccompanying,
    ) {}
}
