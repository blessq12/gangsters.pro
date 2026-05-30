<?php

namespace App\Application\Catalog\DTO;

final readonly class AdminNutritionDTO
{
    public function __construct(
        public float $calories = 0,
        public float $proteins = 0,
        public float $fats = 0,
        public float $carbs = 0,
        public string $basis = 'per_100g',
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            calories: (float) ($data['calories'] ?? 0),
            proteins: (float) ($data['proteins'] ?? 0),
            fats: (float) ($data['fats'] ?? 0),
            carbs: (float) ($data['carbs'] ?? 0),
            basis: (string) ($data['basis'] ?? 'per_100g'),
        );
    }
}
