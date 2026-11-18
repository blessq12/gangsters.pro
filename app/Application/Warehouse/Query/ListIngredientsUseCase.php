<?php

namespace App\Application\Warehouse\Query;

use App\Application\Warehouse\Presenter\IngredientPresenter;
use App\Domain\Warehouse\Repository\IngredientRepository;

final class ListIngredientsUseCase
{
    public function __construct(
        private readonly IngredientRepository $ingredients,
        private readonly IngredientPresenter $presenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(): array
    {
        return $this->presenter->presentList($this->ingredients->listAll());
    }
}
