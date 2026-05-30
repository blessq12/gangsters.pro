<?php

namespace App\Application\Catalog\DTO;

final readonly class UpdateAdminTagDTO
{
    public function __construct(
        public int $id,
        public string $label,
        public string $color,
        public bool $isActive,
        public int $sortOrder,
    ) {
    }
}
