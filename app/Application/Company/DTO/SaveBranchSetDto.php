<?php

namespace App\Application\Company\DTO;

/**
 * Входные данные сохранения набора филиала. Цена — в копейках.
 */
final readonly class SaveBranchSetDto
{
    /**
     * @param  list<array{catalog_item_id: int, quantity: int}>  $lines
     * @param  list<int>  $tagIds
     */
    public function __construct(
        public int $branchId,
        public ?int $id,
        public string $name,
        public ?string $slug,
        public ?string $description,
        public bool $isAvailable,
        public int $priceKopecks,
        public string $status,
        public array $lines,
        public array $tagIds = [],
    ) {}
}
