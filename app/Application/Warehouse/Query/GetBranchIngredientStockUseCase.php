<?php

namespace App\Application\Warehouse\Query;

use App\Application\Warehouse\Presenter\IngredientPresenter;
use App\Application\Warehouse\Presenter\WriteOffPresenter;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;

final class GetBranchIngredientStockUseCase
{
    public function __construct(
        private readonly IngredientRepository $ingredients,
        private readonly StockLevelRepository $levels,
        private readonly StockWriteOffRepository $writeOffs,
        private readonly IngredientPresenter $ingredientPresenter,
        private readonly WriteOffPresenter $writeOffPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId, int $ingredientId): array
    {
        $ingredient = $this->ingredients->findById($ingredientId);
        if ($ingredient === null) {
            throw new \InvalidArgumentException('Ингредиент не найден.');
        }

        $payload = $this->ingredientPresenter->present($ingredient);
        $payload['branch_id'] = $branchId;
        $payload['quantity'] = $this->levels
            ->findByBranchAndIngredient($branchId, $ingredientId)
            ?->quantity() ?? 0.0;
        $payload['write_offs'] = $this->writeOffPresenter->presentList(
            $this->writeOffs->listByBranchAndIngredient($branchId, $ingredientId),
        );

        return $payload;
    }
}
