<?php

namespace App\Application\Catalog\DTO;

final readonly class SaveTagDto
{
    public function __construct(
        public ?int $id,
        public string $code,
        public string $label,
        public string $color,
        public bool $isActive,
    ) {}
}
