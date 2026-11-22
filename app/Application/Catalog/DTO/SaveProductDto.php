<?php

namespace App\Application\Catalog\DTO;

use Illuminate\Http\UploadedFile;

final readonly class SaveProductDto
{
    /**
     * @param  list<array{type: string, amount: int}>  $prices  сумма в рублях
     * @param  list<string>  $ingredients
     * @param  array{calories?: float|int, proteins?: float|int, fats?: float|int, carbs?: float|int, basis?: string}|null  $nutrition
     * @param  list<int>  $tagIds
     * @param  list<array{file: UploadedFile, focus_x: float|null, focus_y: float|null, sort_order?: int|null}>  $newImages
     * @param  list<array{id: int, focus_x: float|null, focus_y: float|null, sort_order: int|null}>  $existingImages
     * @param  list<int>  $deletedImageIds
     */
    public function __construct(
        public ?int $id,
        public string $name,
        public array $prices,
        public ?int $weight,
        public string $status,
        public ?string $description,
        public ?string $sku,
        public array $ingredients = [],
        public ?array $nutrition = null,
        public array $tagIds = [],
        public array $newImages = [],
        public array $existingImages = [],
        public array $deletedImageIds = [],
        public bool $isRoll = false,
        public bool $isComplement = false,
    ) {}
}
