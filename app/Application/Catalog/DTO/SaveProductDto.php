<?php

namespace App\Application\Catalog\DTO;

use Illuminate\Http\UploadedFile;

final readonly class SaveProductDto
{
    /**
     * @param  list<string>  $ingredients
     * @param  array{calories?: float|int, proteins?: float|int, fats?: float|int, carbs?: float|int, basis?: string}|null  $nutrition
     * @param  list<array{file: UploadedFile, focus_x: float|null, focus_y: float|null, sort_order?: int|null}>  $newImages
     * @param  list<array{id: int, focus_x: float|null, focus_y: float|null, sort_order: int|null}>  $existingImages
     * @param  list<int>  $deletedImageIds
     * @param  list<array{ingredient_ref: string, quantity: float, unit: string}>  $techCardLines
     */
    public function __construct(
        public ?int $id,
        public string $name,
        public ?int $weight,
        public string $status,
        public ?string $description,
        public ?string $sku,
        public array $ingredients = [],
        public ?array $nutrition = null,
        public array $newImages = [],
        public array $existingImages = [],
        public array $deletedImageIds = [],
        public bool $isRoll = false,
        public array $techCardLines = [],
    ) {}
}
