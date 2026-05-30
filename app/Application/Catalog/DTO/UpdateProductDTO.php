<?php

namespace App\Application\Catalog\DTO;

final readonly class UpdateProductDTO
{
    /**
     * @param  AdminIngredientDTO[]  $ingredients
     */
    public function __construct(
        public int $productId,
        public string $name,
        public string $description,
        public AdminNutritionDTO $nutrition,
        public array $ingredients,
        public float|string|null $priceRubles = null,
        public ?string $articul = null,
    ) {
    }
}
