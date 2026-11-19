<?php

namespace App\Application\Warehouse\Command;

use App\Application\Warehouse\DTO\SaveIngredientDto;
use App\Application\Warehouse\Presenter\IngredientPresenter;
use App\Domain\Supporting\Warehouse\Entity\Ingredient;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;

final class SaveIngredientUseCase
{
    public function __construct(
        private readonly IngredientRepository $ingredients,
        private readonly IngredientPresenter $presenter,
    ) {}

    /**
     * @return array{ingredient: array<string, mixed>, created: bool}
     */
    public function execute(SaveIngredientDto $dto): array
    {
        if ($dto->id === null) {
            $ingredient = Ingredient::create($dto->name, $dto->sku, $dto->unit);
            $this->ingredients->save($ingredient);

            return [
                'ingredient' => $this->presenter->present($ingredient),
                'created' => true,
            ];
        }

        if ($this->ingredients->findById($dto->id) === null) {
            throw new \InvalidArgumentException('Ингредиент не найден.');
        }

        $validated = Ingredient::create($dto->name, $dto->sku, $dto->unit);
        $ingredient = Ingredient::restore(
            id: $dto->id,
            name: $validated->name(),
            sku: $validated->sku(),
            unit: $validated->unit(),
        );
        $this->ingredients->save($ingredient);

        return [
            'ingredient' => $this->presenter->present($ingredient),
            'created' => false,
        ];
    }
}
