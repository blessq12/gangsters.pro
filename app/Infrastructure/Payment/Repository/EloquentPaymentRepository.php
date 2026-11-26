<?php

namespace App\Infrastructure\Payment\Repository;

use App\Domain\Supporting\Payment\Entity\Payment;
use App\Domain\Supporting\Payment\Repository\PaymentRepository;
use App\Infrastructure\Payment\Mapper\PaymentMapper;
use App\Infrastructure\Payment\Model\PaymentModel;
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
        $row = PaymentModel::query()->find($id);

        return $row instanceof PaymentModel ? $this->mapper->toDomain($row) : null;
    }

    public function findByOrderId(int $orderId): ?Payment
    {
        $row = PaymentModel::query()
            ->where('order_id', $orderId)
            ->first();

        return $row instanceof PaymentModel ? $this->mapper->toDomain($row) : null;
    }

    public function add(Payment $payment): void
    {
        if ($payment->hasId()) {
            throw new \InvalidArgumentException('Платёж уже сохранён — используй save.');
        }

        $row = PaymentModel::query()->create($this->mapper->toPersistence($payment));
        $payment->assignId((int) $row->id);
    }

    public function save(Payment $payment): void
    {
        if (! $payment->hasId()) {
            throw new \InvalidArgumentException('Платёж ещё не создан — используй add.');
        }

        PaymentModel::query()
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
        ?int $branchId = null,
    ): array {
        $rows = $this->filteredQuery($createdFrom, $createdTo, $status, $method, $branchId)
            ->orderByDesc('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            if ($row instanceof PaymentModel) {
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
        ?int $branchId = null,
    ): int {
        return $this->filteredQuery($createdFrom, $createdTo, $status, $method, $branchId)->count();
    }

    public function summarizeFiltered(
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?string $status = null,
        ?string $method = null,
        ?int $branchId = null,
    ): array {
        $totals = $this->filteredQuery($createdFrom, $createdTo, $status, $method, $branchId)
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

        $byMethodRows = $this->filteredQuery($createdFrom, $createdTo, $status, $method, $branchId)
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
     * @return Builder<PaymentModel>
     */
    private function filteredQuery(
        ?DateTimeImmutable $createdFrom,
        ?DateTimeImmutable $createdTo,
        ?string $status,
        ?string $method,
        ?int $branchId = null,
    ): Builder {
        $query = PaymentModel::query();

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
        if ($branchId !== null && $branchId > 0) {
            $query->whereIn('order_id', static function ($sub) use ($branchId): void {
                $sub->select('id')
                    ->from('ORD_orders')
                    ->where('branch_id', $branchId);
            });
        }

        return $query;
    }
}
