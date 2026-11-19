<?php

namespace App\Application\Catalog\DTO;

use Illuminate\Http\UploadedFile;

final readonly class SaveSetDto
{
    /**
     * @param  list<array{type: string, amount: int}>  $prices  сумма в рублях
     * @param  list<array{product_id: int, quantity: int}>|null  $lines  null — не трогать состав
     * @param  list<array{file: UploadedFile, focus_x: float|null, focus_y: float|null, sort_order?: int|null}>  $newImages
     * @param  list<array{id: int, focus_x: float|null, focus_y: float|null, sort_order: int|null}>  $existingImages
     * @param  list<int>  $deletedImageIds
     */
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $sku,
        public array $prices,
        public string $status,
        public ?string $description = null,
        public ?array $lines = null,
        public array $newImages = [],
        public array $existingImages = [],
        public array $deletedImageIds = [],
    ) {}
}
