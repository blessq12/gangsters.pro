<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;

final class ListActiveRouteSheetsUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly RouteSheetPresenter $presenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(): array
    {
        return $this->presenter->presentList($this->sheets->listActive());
    }
}
