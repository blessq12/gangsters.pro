<?php

namespace App\Application\Payment\Query;

use App\Domain\Supporting\Payment\Repository\PaymentRepository;
use DateTimeImmutable;

final class GetPaymentSummaryUseCase
{
    public function __construct(
        private readonly PaymentRepository $payments,
    ) {}

    /**
     * @return array{
     *     paid_kopecks: int,
     *     refunded_kopecks: int,
     *     pending_kopecks: int,
     *     paid_count: int,
     *     refunded_count: int,
     *     pending_count: int,
     *     cancelled_count: int,
     *     paid_by_method: array<string, int>,
     *     average_paid_kopecks: int|null,
     *     from: string|null,
     *     to: string|null
     * }
     */
    public function execute(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
        ?int $branchId = null,
    ): array {
        $statusFilter = $status !== null && trim($status) !== '' ? trim($status) : null;
        $methodFilter = $method !== null && trim($method) !== '' ? trim($method) : null;
        $branchFilter = $branchId !== null && $branchId > 0 ? $branchId : null;

        $summary = $this->payments->summarizeFiltered(
            $createdFrom,
            $createdTo,
            $statusFilter,
            $methodFilter,
            $branchFilter,
        );

        return [
            ...$summary,
            'from' => $createdFrom?->format(DATE_ATOM),
            'to' => $createdTo?->format(DATE_ATOM),
        ];
    }
}
