<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\Exception\RouteSheetNotFound;
use App\Domain\Delivery\Entity\RouteSheet;
use App\Domain\Delivery\Repository\RouteSheetRepository;

final class TakeRouteSheetUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
    ) {}

    public function execute(int $sheetId): RouteSheet
    {
        $sheet = $this->sheets->findById($sheetId);
        if ($sheet === null) {
            throw RouteSheetNotFound::forId($sheetId);
        }

        $taken = $sheet->take();
        $this->sheets->save($taken);

        return $taken;
    }
}
