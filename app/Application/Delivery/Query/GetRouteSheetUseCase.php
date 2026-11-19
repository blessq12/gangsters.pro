<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;

final class GetRouteSheetUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly RouteSheetPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $sheetId): array
    {
        $sheet = $this->sheets->findById($sheetId);
        if ($sheet === null) {
            throw new \InvalidArgumentException('Маршрутный лист не найден.');
        }

        return $this->presenter->present($sheet);
    }
}
