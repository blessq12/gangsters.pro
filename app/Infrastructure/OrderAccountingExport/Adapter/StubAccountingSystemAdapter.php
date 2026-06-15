<?php

namespace App\Infrastructure\OrderAccountingExport\Adapter;

use App\Application\OrderAccountingExport\Port\AccountingSystemAdapter;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\ValueObject\ExportResult;

/**
 * Заглушка системы учёта для разработки и e2e.
 */
final class StubAccountingSystemAdapter implements AccountingSystemAdapter
{
    public function systemCode(): string
    {
        return 'stub';
    }

    public function isEnabled(): bool
    {
        return (bool) config('order-accounting-export.systems.stub.enabled', false);
    }

    public function supports(OrderCreated $event): bool
    {
        return true;
    }

    public function export(OrderCreated $event): ExportResult
    {
        return ExportResult::success(
            externalReference: sprintf('stub-%d', $event->orderId()->value()),
        );
    }
}
