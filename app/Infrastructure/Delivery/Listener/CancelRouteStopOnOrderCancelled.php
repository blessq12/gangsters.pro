<?php

namespace App\Infrastructure\Delivery\Listener;

use App\Domain\Delivery\Repository\RouteSheetRepository;
use App\Domain\Order\Event\OrderCancelled;

/**
 * Order публикует отмену; Delivery помечает остановку активного листа отменённой.
 */
final class CancelRouteStopOnOrderCancelled
{
    public function __construct(
        private readonly RouteSheetRepository $sheets,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        $sheet = $this->sheets->findOpenByOrderId($event->orderId);
        if ($sheet === null) {
            return;
        }

        $this->sheets->save($sheet->cancelStop($event->orderId));
    }
}
