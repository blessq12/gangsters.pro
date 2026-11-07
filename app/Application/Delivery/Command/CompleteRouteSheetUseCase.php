<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\Exception\RouteSheetNotFound;
use App\Domain\Delivery\Entity\RouteSheet;
use App\Domain\Delivery\Repository\RouteSheetRepository;

final class CompleteRouteSheetUseCase
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

        $completed = $sheet->complete();
        $this->sheets->save($completed);

        return $completed;
    }
}
