<?php

namespace App\Application\Warehouse\Presenter;

use App\Domain\Supporting\Warehouse\Entity\Ingredient;

final class IngredientPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Ingredient $ingredient): array
    {
        return [
            'id' => $ingredient->hasId() ? $ingredient->id() : null,
            'name' => $ingredient->name(),
            'unit' => $ingredient->unit(),
        ];
    }

    /**
     * @param  list<Ingredient>  $ingredients
     * @return list<array<string, mixed>>
     */
    public function presentList(array $ingredients): array
    {
        return array_map(fn (Ingredient $ingredient): array => $this->present($ingredient), $ingredients);
    }
}
