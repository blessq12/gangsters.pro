<?php

namespace App\Domain\Order\Port;

use App\Domain\Order\Event\OrderCreated;

/**
 * Outbound port: push a newly created order to Frontpad (best-effort).
 */
interface FrontpadOrderExporter
{
    public function export(OrderCreated $event): void;
}
