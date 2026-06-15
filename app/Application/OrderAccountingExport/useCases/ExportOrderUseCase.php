<?php

namespace App\Application\OrderAccountingExport\useCases;

use App\Application\OrderAccountingExport\Services\AccountingAdapterRegistry;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\Repository\ExportAttemptRepository;
use App\Domain\OrderAccountingExport\ValueObject\ExportResult;

/**
 * Сценарий: отправить один заказ в одну систему учёта.
 */
final class ExportOrderUseCase
{
    public function __construct(
        private readonly AccountingAdapterRegistry $adapterRegistry,
        private readonly ExportAttemptRepository $exportAttempts,
    ) {}

    public function execute(OrderCreated $event, string $systemCode): ExportResult
    {
        $orderId = $event->orderId()->value();

        if ($this->exportAttempts->hasSuccessfulExport($orderId, $systemCode)) {
            return ExportResult::success();
        }

        $adapter = $this->adapterRegistry->resolve($systemCode);

        if (! $adapter->supports($event)) {
            return ExportResult::failed('Заказ не подходит для выбранной системы учёта.');
        }

        $attempt = $this->exportAttempts->nextAttemptNumber($orderId, $systemCode);
        $result = $adapter->export($event);

        $this->exportAttempts->record(
            orderId: $orderId,
            systemCode: $systemCode,
            status: $result->status(),
            attempt: $attempt,
            externalReference: $result->externalReference(),
            errorMessage: $result->errorMessage(),
        );

        return $result;
    }
}
