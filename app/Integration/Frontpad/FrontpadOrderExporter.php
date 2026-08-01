<?php

namespace App\Integration\Frontpad;

use App\Domain\Order\Event\OrderCreated;

/**
 * Outbound port интеграции: выгрузка заказа во Frontpad (best-effort).
 */
interface FrontpadOrderExporter
{
    public function export(OrderCreated $event): void;
}
