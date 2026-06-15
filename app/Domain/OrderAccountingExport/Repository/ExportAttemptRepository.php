<?php

namespace App\Domain\OrderAccountingExport\Repository;

use App\Domain\OrderAccountingExport\Enum\ExportAttemptStatus;

interface ExportAttemptRepository
{
    public function hasSuccessfulExport(int $orderId, string $systemCode): bool;

    public function nextAttemptNumber(int $orderId, string $systemCode): int;

    public function record(
        int $orderId,
        string $systemCode,
        ExportAttemptStatus $status,
        int $attempt,
        ?string $externalReference = null,
        ?string $errorMessage = null,
    ): void;
}
