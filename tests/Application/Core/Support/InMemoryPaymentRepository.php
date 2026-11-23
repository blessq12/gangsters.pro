<?php

namespace Tests\Application\Core\Support;

use App\Domain\Supporting\Payment\Entity\Payment;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;
use DateTimeImmutable;

final class InMemoryPaymentRepository implements PaymentRepository
{
    public function findById(int $id): ?Payment
    {
        return null;
    }

    public function findByOrderId(int $orderId): ?Payment
    {
        return null;
    }

    public function add(Payment $payment): void {}

    public function save(Payment $payment): void {}

    public function listPage(
        int $offset,
        int $limit,
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
    ): array {
        return [];
    }

    public function countFiltered(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
    ): int {
        return 0;
    }

    public function summarizeFiltered(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
        ?int $branchId = null,
    ): array {
        return [
            'paid_kopecks' => 0,
            'refunded_kopecks' => 0,
            'pending_kopecks' => 0,
            'paid_count' => 0,
            'refunded_count' => 0,
            'pending_count' => 0,
            'cancelled_count' => 0,
            'paid_by_method' => [],
            'average_paid_kopecks' => null,
        ];
    }
}
