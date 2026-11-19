<?php

namespace App\Application\Warehouse\Query;

use App\Application\Shared\PageParams;
use App\Application\Warehouse\Presenter\StockLevelPresenter;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;

final class ListStockLevelsUseCase
{
    public function __construct(
        private readonly StockLevelRepository $levels,
        private readonly StockLevelPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            $this->presenter->presentList($this->levels->listPage($offset, $limit)),
            $this->levels->countAll(),
            $offset,
            $limit,
        );
    }
}
