<?php

namespace App\Application\Menu\DTO;

/**
 * Входные данные сохранения тега филиала.
 */
final readonly class SaveBranchTagDto
{
    public function __construct(
        public int $branchId,
        public ?int $id,
        public string $code,
        public string $label,
        public string $color,
        public bool $isActive,
        public int $sortOrder,
    ) {}
}
