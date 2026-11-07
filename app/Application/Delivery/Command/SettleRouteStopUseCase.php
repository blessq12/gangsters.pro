<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\Exception\RouteSheetNotFound;
use App\Application\Order\Command\ChangeAdminOrderStatusUseCase;
use App\Domain\Delivery\Entity\RouteSheet;
use App\Domain\Delivery\Repository\RouteSheetRepository;
use App\Domain\Order\Enum\OrderStatus;
use Illuminate\Support\Facades\DB;

final class SettleRouteStopUseCase
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
        private readonly ChangeAdminOrderStatusUseCase $changeStatus,
    ) {}

    public function execute(int $sheetId, int $orderId, string $outcome, ?string $reason = null): RouteSheet
    {
        $sheet = $this->sheets->findById($sheetId);
        if ($sheet === null) {
            throw RouteSheetNotFound::forId($sheetId);
        }

        $settled = $sheet->settle($orderId, $outcome, (string) $reason);

        return DB::transaction(function () use ($settled, $orderId, $outcome): RouteSheet {
            $this->sheets->save($settled);
            $this->changeStatus->execute(
                $orderId,
                $outcome === 'delivered' ? OrderStatus::Delivered->value : OrderStatus::Ready->value,
            );

            return $settled;
        });
    }
}
