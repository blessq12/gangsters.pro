<?php

namespace App\Application\Warehouse\Query;

use App\Application\Shared\PageParams;
use App\Application\Warehouse\Presenter\WriteOffPresenter;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;

final class ListWriteOffsUseCase
{
    public function __construct(
        private readonly StockWriteOffRepository $writeOffs,
        private readonly WriteOffPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            $this->presenter->presentList($this->writeOffs->listPage($offset, $limit)),
            $this->writeOffs->countAll(),
            $offset,
            $limit,
        );
    }
}
