<?php

namespace App\Domain\Supporting\Payment\Repository;

use App\Domain\Supporting\Payment\Entity\Payment;
use DateTimeImmutable;

interface PaymentRepository
{
    public function findById(int $id): ?Payment;

    public function findByOrderId(int $orderId): ?Payment;

    /** Сохранить новый платёж (назначает id). */
    public function add(Payment $payment): void;

    public function save(Payment $payment): void;

    /**
     * Журнал для бэкофиса: фильтр по created_at / status / method.
     *
     * @return list<Payment>
     */
    public function listPage(
        int $offset,
        int $limit,
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
    ): array;

    public function countFiltered(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
    ): int;

    /**
     * Сводка по тем же фильтрам, что и журнал.
     *
     * @return array{
     *     paid_kopecks: int,
     *     refunded_kopecks: int,
     *     pending_kopecks: int,
     *     paid_count: int,
     *     refunded_count: int,
     *     pending_count: int,
     *     cancelled_count: int,
     *     paid_by_method: array<string, int>,
     *     average_paid_kopecks: int|null
     * }
     */
    public function summarizeFiltered(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
        ?int $branchId = null,
    ): array;
}
