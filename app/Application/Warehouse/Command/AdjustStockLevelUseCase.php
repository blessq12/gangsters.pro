<?php

namespace App\Application\Warehouse\Command;

use App\Application\Warehouse\DTO\AdjustStockLevelDto;
use App\Application\Warehouse\Presenter\StockLevelPresenter;
use App\Domain\Supporting\Warehouse\Entity\StockLevel;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;

final class AdjustStockLevelUseCase
{
    public function __construct(
        private readonly IngredientRepository $ingredients,
        private readonly StockLevelRepository $levels,
        private readonly StockLevelPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(AdjustStockLevelDto $dto): array
    {
        if ($this->ingredients->findById($dto->ingredientId) === null) {
            throw new \InvalidArgumentException('Ингредиент не найден.');
        }

        $current = $this->levels->findByIngredientId($dto->ingredientId);
        $currentQty = $current?->quantity() ?? 0.0;
        $nextQty = $currentQty + $dto->delta;

        if ($nextQty < 0) {
            throw new \InvalidArgumentException('Остаток не может стать отрицательным.');
        }

        $level = new StockLevel($dto->ingredientId, $nextQty);
        $this->levels->save($level);

        return $this->presenter->present($level);
    }
}
