<?php

namespace App\Infrastructure\Payment\Repository;

use App\Domain\Supporting\Payment\Entity\Payment;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;
use App\Infrastructure\Payment\Mapper\PaymentMapper;
use App\Infrastructure\Payment\Model\PAY_Payment;
use App\Shared\Enum\PaymentMethod;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentPaymentRepository implements PaymentRepository
{
    public function __construct(
        private readonly PaymentMapper $mapper,
    ) {}

    public function findById(int $id): ?Payment
    {
        $row = PAY_Payment::query()->find($id);

        return $row instanceof PAY_Payment ? $this->mapper->toDomain($row) : null;
    }

    public function findByOrderId(int $orderId): ?Payment
    {
        $row = PAY_Payment::query()
            ->where('order_id', $orderId)
            ->first();

        return $row instanceof PAY_Payment ? $this->mapper->toDomain($row) : null;
    }

    public function add(Payment $payment): void
    {
        if ($payment->hasId()) {
            throw new \InvalidArgumentException('Платёж уже сохранён — используй save.');
        }

        $row = PAY_Payment::query()->create($this->mapper->toPersistence($payment));
        $payment->assignId((int) $row->id);
    }

    public function save(Payment $payment): void
    {
        if (! $payment->hasId()) {
            throw new \InvalidArgumentException('Платёж ещё не создан — используй add.');
        }

        PAY_Payment::query()
            ->whereKey($payment->id())
            ->update($this->mapper->toPersistence($payment));
    }

    public function listPage(
        int $offset,
        int $limit,
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
    ): array {
        $rows = $this->filteredQuery($createdFrom, $createdTo, $status, $method)
            ->orderByDesc('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            if ($row instanceof PAY_Payment) {
                $items[] = $this->mapper->toDomain($row);
            }
        }

        return $items;
    }

    public function countFiltered(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
    ): int {
        return $this->filteredQuery($createdFrom, $createdTo, $status, $method)->count();
    }

    public function summarizeFiltered(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
    ): array {
        $totals = $this->filteredQuery($createdFrom, $createdTo, $status, $method)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount_kopecks ELSE 0 END), 0) AS paid_kopecks,
                COALESCE(SUM(CASE WHEN status = 'refunded' THEN amount_kopecks ELSE 0 END), 0) AS refunded_kopecks,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount_kopecks ELSE 0 END), 0) AS pending_kopecks,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END), 0) AS paid_count,
                COALESCE(SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END), 0) AS refunded_count,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS pending_count,
                COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) AS cancelled_count,
                AVG(CASE WHEN status = 'paid' THEN amount_kopecks END) AS average_paid_kopecks
            ")
            ->first();

        $byMethodRows = $this->filteredQuery($createdFrom, $createdTo, $status, $method)
            ->where('status', 'paid')
            ->selectRaw('method, COALESCE(SUM(amount_kopecks), 0) AS total_kopecks')
            ->groupBy('method')
            ->get();

        $paidByMethod = [];
        foreach (PaymentMethod::cases() as $paymentMethod) {
            $paidByMethod[$paymentMethod->value] = 0;
        }
        foreach ($byMethodRows as $row) {
            $key = (string) $row->method;
            $paidByMethod[$key] = (int) $row->total_kopecks;
        }

        $average = $totals?->average_paid_kopecks;
        $averageKopecks = $average === null ? null : (int) round((float) $average);

        return [
            'paid_kopecks' => (int) ($totals->paid_kopecks ?? 0),
            'refunded_kopecks' => (int) ($totals->refunded_kopecks ?? 0),
            'pending_kopecks' => (int) ($totals->pending_kopecks ?? 0),
            'paid_count' => (int) ($totals->paid_count ?? 0),
            'refunded_count' => (int) ($totals->refunded_count ?? 0),
            'pending_count' => (int) ($totals->pending_count ?? 0),
            'cancelled_count' => (int) ($totals->cancelled_count ?? 0),
            'paid_by_method' => $paidByMethod,
            'average_paid_kopecks' => $averageKopecks,
        ];
    }

    /**
     * @return Builder<PAY_Payment>
     */
    private function filteredQuery(
        ?DateTimeImmutable $createdFrom,
        ?DateTimeImmutable $createdTo,
        ?string $status,
        ?string $method,
    ): Builder {
        $query = PAY_Payment::query();

        if ($createdFrom !== null) {
            $query->where('created_at', '>=', $createdFrom->format('Y-m-d H:i:s'));
        }
        if ($createdTo !== null) {
            $query->where('created_at', '<=', $createdTo->format('Y-m-d H:i:s'));
        }
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        if ($method !== null && $method !== '') {
            $query->where('method', $method);
        }

        return $query;
    }
}
