<?php

namespace App\Application\OrderAccountingExport\Port;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\ValueObject\ExportResult;

interface AccountingSystemAdapter
{
    public function systemCode(): string;

    public function isEnabled(): bool;

    public function supports(OrderCreated $event): bool;

    public function export(OrderCreated $event): ExportResult;
}
