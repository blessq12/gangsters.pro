<?php

namespace App\Application\Warehouse\Query;

use App\Application\Warehouse\Presenter\IngredientPresenter;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;

final class GetIngredientUseCase
{
    public function __construct(
        private readonly IngredientRepository $ingredients,
        private readonly IngredientPresenter $ingredientPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $id): array
    {
        $ingredient = $this->ingredients->findById($id);
        if ($ingredient === null) {
            throw new \InvalidArgumentException('Ингредиент не найден.');
        }

        return $this->ingredientPresenter->present($ingredient);
    }
}
