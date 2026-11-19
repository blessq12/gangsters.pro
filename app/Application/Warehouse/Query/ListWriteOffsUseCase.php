<?php

namespace App\Application\Warehouse\Query;

use App\Application\Warehouse\Presenter\WriteOffPresenter;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;

final class ListWriteOffsUseCase
{
    public function __construct(
        private readonly StockWriteOffRepository $writeOffs,
        private readonly WriteOffPresenter $presenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(): array
    {
        return $this->presenter->presentList($this->writeOffs->listAll());
    }
}
