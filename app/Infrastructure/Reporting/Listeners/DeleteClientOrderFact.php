<?php

namespace App\Infrastructure\Reporting\Listeners;

use App\Application\Order\Events\OrderCancelledIntegrationEvent;
use App\Infrastructure\Reporting\Model\ReportingClientOrderFact;

final class DeleteClientOrderFact
{
    public function handle(OrderCancelledIntegrationEvent $event): void
    {
        ReportingClientOrderFact::query()
            ->where('order_id', $event->orderId)
            ->delete();
    }
}
