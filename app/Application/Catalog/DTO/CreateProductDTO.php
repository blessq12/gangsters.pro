<?php

namespace App\Application\Catalog\DTO;

final readonly class CreateProductDTO
{
    /**
     * @param  AdminIngredientDTO[]  $ingredients
     */
    public function __construct(
        public string $name,
        public string $description,
        public AdminNutritionDTO $nutrition,
        public array $ingredients,
        public float|string|null $priceRubles = null,
        public ?string $articul = null,
    ) {
    }
}
