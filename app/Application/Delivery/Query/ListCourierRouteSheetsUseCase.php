<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\RouteSheetPresenter;
use App\Domain\Core\Delivery\Routing\Repository\RouteSheetRepository;

final class ListCourierRouteSheetsUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly RouteSheetPresenter $presenter,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(int $courierId): array
    {
        if ($courierId < 1) {
            throw new \InvalidArgumentException('courierId должен быть положительным.');
        }

        $own = [];
        foreach ($this->sheets->listActive() as $sheet) {
            if ($sheet->courierId() === $courierId) {
                $own[] = $sheet;
            }
        }

        return $this->presenter->presentList($own, hideCancelled: true);
    }
}
