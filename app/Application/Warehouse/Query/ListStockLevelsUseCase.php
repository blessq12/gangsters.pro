<?php

namespace App\Application\Warehouse\Query;

use App\Application\Warehouse\Presenter\StockLevelPresenter;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;

final class ListStockLevelsUseCase
{
    public function __construct(
        private readonly StockLevelRepository $levels,
        private readonly StockLevelPresenter $presenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(): array
    {
        return $this->presenter->presentList($this->levels->listAll());
    }
}
