<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;

final class ListActiveRouteSheetsUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly RouteSheetPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $branchId, int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        if ($branchId < 1) {
            throw new \InvalidArgumentException('Некорректный branch_id.');
        }

        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            $this->presenter->presentList($this->sheets->listActivePageByBranch($branchId, $offset, $limit)),
            $this->sheets->countActiveByBranch($branchId),
            $offset,
            $limit,
        );
    }
}
