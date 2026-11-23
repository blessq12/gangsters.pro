<?php

namespace App\Application\Warehouse\Command;

use App\Application\Warehouse\DTO\WriteOffStockDto;
use App\Application\Warehouse\Presenter\WriteOffPresenter;
use App\Domain\Supporting\Warehouse\Entity\StockLevel;
use App\Domain\Supporting\Warehouse\Entity\StockWriteOff;
use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;

final class WriteOffStockUseCase
{
    public function __construct(
        private readonly IngredientRepository $ingredients,
        private readonly StockLevelRepository $levels,
        private readonly StockWriteOffRepository $writeOffs,
        private readonly WriteOffPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(WriteOffStockDto $dto): array
    {
        if ($this->ingredients->findById($dto->ingredientId) === null) {
            throw new \InvalidArgumentException('Ингредиент не найден.');
        }

        $current = $this->levels->findByBranchAndIngredient($dto->branchId, $dto->ingredientId);
        $currentQty = $current?->quantity() ?? 0.0;
        if ($currentQty < $dto->quantity) {
            throw new \InvalidArgumentException('Недостаточно остатка для списания.');
        }

        $writeOff = StockWriteOff::record(
            branchId: $dto->branchId,
            ingredientId: $dto->ingredientId,
            quantity: $dto->quantity,
            reason: $dto->reason,
            kitchenTicketId: $dto->kitchenTicketId,
        );
        $this->writeOffs->save($writeOff);

        $this->levels->save(new StockLevel(
            branchId: $dto->branchId,
            ingredientId: $dto->ingredientId,
            quantity: $currentQty - $dto->quantity,
        ));

        return $this->presenter->present($writeOff);
    }
}
