<?php

namespace App\Application\Menu\DTO;

/**
 * Входные данные сохранения категории филиала.
 */
final readonly class SaveBranchCategoryDto
{
    /**
     * @param  list<array{entry_kind: string, entry_id: int, sort_order: int}>|null  $items  null — не трогать состав
     */
    public function __construct(
        public int $branchId,
        public ?int $id,
        public string $name,
        public ?string $slug,
        public int $sortOrder,
        public bool $isActive,
        public bool $isAccompanying,
        public bool $isComplement = false,
        public bool $isGift = false,
        public ?array $items = null,
    ) {}
}
