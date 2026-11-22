<?php

namespace App\Application\Company\DTO;

/**
 * Входные данные upsert позиции меню филиала. Цена — в копейках.
 */
final readonly class SaveBranchMenuItemDto
{
    public function __construct(
        public int $branchId,
        public int $catalogItemId,
        public bool $isAvailable,
        public int $priceKopecks,
    ) {}
}
