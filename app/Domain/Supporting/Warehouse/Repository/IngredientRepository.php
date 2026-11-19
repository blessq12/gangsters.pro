<?php

namespace App\Domain\Supporting\Warehouse\Repository;

use App\Domain\Supporting\Warehouse\Entity\Ingredient;

interface IngredientRepository
{
    public function findById(int $id): ?Ingredient;

    public function findByName(string $name): ?Ingredient;

    /**
     * @return list<Ingredient>
     */
    public function listAll(): array;

    public function save(Ingredient $ingredient): void;
}
