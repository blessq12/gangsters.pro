<?php

namespace App\Infrastructure\Reporting\Listeners;

use App\Application\Order\Events\OrderCreatedIntegrationEvent;
use App\Application\Order\Events\OrderUpdatedIntegrationEvent;
use App\Infrastructure\Reporting\Model\ReportingClientOrderFact;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

final class UpsertClientOrderFact
{
    private static ?bool $tableExists = null;

    public function handle(OrderCreatedIntegrationEvent|OrderUpdatedIntegrationEvent $event): void
    {
        if (! $this->projectionTableExists()) {
            return;
        }

        if ($event->clientId === null) {
            return;
        }

        $existing = ReportingClientOrderFact::query()->find($event->orderId);
        $createdAt = $existing?->created_at;

        if ($event instanceof OrderCreatedIntegrationEvent && $event->createdAt !== null) {
            $createdAt = Carbon::parse($event->createdAt);
        }

        ReportingClientOrderFact::query()->updateOrCreate(
            ['order_id' => $event->orderId],
            [
                'client_id' => $event->clientId,
                'payment_status' => $event->paymentStatus,
                'total' => $event->total,
                'created_at' => $createdAt ?? Carbon::now(),
                'updated_at' => $event instanceof OrderUpdatedIntegrationEvent
                    ? Carbon::parse($event->updatedAt)
                    : ($createdAt ?? Carbon::now()),
            ],
        );
    }

    private function projectionTableExists(): bool
    {
        if (self::$tableExists === null) {
            self::$tableExists = Schema::hasTable('reporting_client_order_facts');
        }

        return self::$tableExists;
    }
}
