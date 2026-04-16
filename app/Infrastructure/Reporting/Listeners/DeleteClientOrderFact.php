<?php

namespace App\Infrastructure\Reporting\Listeners;

use App\Application\Order\Events\OrderCancelledIntegrationEvent;
use App\Infrastructure\Reporting\Model\ReportingClientOrderFact;
use Illuminate\Support\Facades\Schema;

final class DeleteClientOrderFact
{
    private static ?bool $tableExists = null;

    public function handle(OrderCancelledIntegrationEvent $event): void
    {
        if (! $this->projectionTableExists()) {
            return;
        }

        ReportingClientOrderFact::query()
            ->where('order_id', $event->orderId)
            ->delete();
    }

    private function projectionTableExists(): bool
    {
        if (self::$tableExists === null) {
            self::$tableExists = Schema::hasTable('reporting_client_order_facts');
        }

        return self::$tableExists;
    }
}
