<?php

namespace App\Application\Warehouse\DTO;

final readonly class SaveIngredientDto
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $unit,
    ) {}
}
