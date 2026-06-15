<?php

namespace App\Application\OrderAccountingExport\Handler;

use App\Application\OrderAccountingExport\useCases\ExportOrderUseCase;
use App\Application\OrderAccountingExport\Services\AccountingAdapterRegistry;
use App\Domain\Order\Event\OrderCreated;

/**
 * Observer: после создания заказа — fan-out в включённые системы учёта.
 */
final class OnOrderCreated
{
    public function __construct(
        private readonly AccountingAdapterRegistry $adapterRegistry,
        private readonly ExportOrderUseCase $exportOrder,
    ) {}

    public function handle(OrderCreated $event): void
    {
        foreach ($this->adapterRegistry->enabled() as $adapter) {
            if (! $adapter->supports($event)) {
                continue;
            }

            $this->exportOrder->execute($event, $adapter->systemCode());
        }
    }
}
