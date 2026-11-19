<?php

namespace App\Application\Catalog\DTO;

final readonly class SaveCategoryDto
{
    /**
     * @param  list<array{item_id: int, sort_order?: int}>|null  $items  null — не трогать состав
     */
    public function __construct(
        public ?int $id,
        public string $name,
        public bool $isActive,
        public bool $isAccompanying,
        public ?array $items = null,
    ) {}
}
