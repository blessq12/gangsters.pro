<?php

namespace App\Application\Catalog\DTO;

final readonly class CreateAdminTagDTO
{
    public function __construct(
        public string $label,
        public string $color = 'amber',
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {
    }
}
