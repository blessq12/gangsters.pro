<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\Ingredient;

interface IngredientRepository
{
    public function findById(int $id): ?Ingredient;

    /**
     * @return list<Ingredient>
     */
    public function listAll(): array;

    public function save(Ingredient $ingredient): void;
}
