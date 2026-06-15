<?php

namespace App\Infrastructure\OrderAccountingExport\Repository;

use App\Domain\OrderAccountingExport\Enum\ExportAttemptStatus;
use App\Domain\OrderAccountingExport\Repository\ExportAttemptRepository;
use App\Infrastructure\OrderAccountingExport\Model\OAE_ExportAttempt;
use DateTimeImmutable;

final class EloquentExportAttemptRepository implements ExportAttemptRepository
{
    public function hasSuccessfulExport(int $orderId, string $systemCode): bool
    {
        return OAE_ExportAttempt::query()
            ->where('order_id', $orderId)
            ->where('system_code', $systemCode)
            ->where('status', ExportAttemptStatus::Success->value)
            ->exists();
    }

    public function nextAttemptNumber(int $orderId, string $systemCode): int
    {
        $lastAttempt = OAE_ExportAttempt::query()
            ->where('order_id', $orderId)
            ->where('system_code', $systemCode)
            ->max('attempt');

        return ((int) $lastAttempt) + 1;
    }

    public function record(
        int $orderId,
        string $systemCode,
        ExportAttemptStatus $status,
        int $attempt,
        ?string $externalReference = null,
        ?string $errorMessage = null,
    ): void {
        OAE_ExportAttempt::query()->create([
            'order_id' => $orderId,
            'system_code' => $systemCode,
            'status' => $status->value,
            'attempt' => $attempt,
            'external_reference' => $externalReference,
            'error_message' => $errorMessage,
            'created_at' => new DateTimeImmutable(),
        ]);
    }
}
