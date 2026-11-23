<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderNumber;
use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Order\Model\ORD_OrderLine;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentOrderRepository implements OrderRepository
{
    public function __construct(
        private readonly OrderMapper $mapper,
    ) {}

    public function findById(int $id): ?Order
    {
        $row = ORD_Order::query()->with('lines')->find($id);

        return $row instanceof ORD_Order ? $this->mapper->toDomain($row) : null;
    }

    public function findByClientRequestId(string $clientRequestId): ?Order
    {
        $row = ORD_Order::query()
            ->with('lines')
            ->where('checkout_id', $clientRequestId)
            ->first();

        return $row instanceof ORD_Order ? $this->mapper->toDomain($row) : null;
    }

    public function count(array $filters = []): int
    {
        return $this->filteredQuery($filters)->count();
    }

    public function countByStatus(): array
    {
        $counts = [];
        $rows = ORD_Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->get();

        foreach ($rows as $row) {
            $counts[(string) $row->status] = (int) $row->aggregate;
        }

        return $counts;
    }

    public function countByStatusFiltered(
        ?int $branchId = null,
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
    ): array {
        $query = ORD_Order::query();
        if ($branchId !== null && $branchId > 0) {
            $query->where('branch_id', $branchId);
        }
        if ($createdFrom !== null) {
            $query->where('created_at', '>=', $createdFrom->format('Y-m-d H:i:s'));
        }
        if ($createdTo !== null) {
            $query->where('created_at', '<=', $createdTo->format('Y-m-d H:i:s'));
        }

        $counts = [];
        $rows = $query
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->get();

        foreach ($rows as $row) {
            $counts[(string) $row->status] = (int) $row->aggregate;
        }

        return $counts;
    }

    public function countOpsSnapshot(?int $branchId = null): array
    {
        $base = ORD_Order::query();
        if ($branchId !== null && $branchId > 0) {
            $base->where('branch_id', $branchId);
        }

        $byStatus = [];
        $rows = (clone $base)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->get();
        foreach ($rows as $row) {
            $byStatus[(string) $row->status] = (int) $row->aggregate;
        }

        $readyCourier = (int) (clone $base)
            ->where('status', 'ready')
            ->where('delivery_type', 'courier')
            ->count();

        return [
            'new' => (int) ($byStatus['new'] ?? 0),
            'preparing' => (int) ($byStatus['preparing'] ?? 0),
            'ready' => (int) ($byStatus['ready'] ?? 0),
            'ready_courier' => $readyCourier,
            'in_transit' => (int) ($byStatus['in_transit'] ?? 0),
        ];
    }

    public function listPage(int $offset, int $limit, array $filters = []): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ORD_Order> $rows */
        $rows = $this->filteredQuery($filters)
            ->with('lines')
            ->orderByDesc('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get();

        return $rows
            ->map(fn (ORD_Order $row): Order => $this->mapper->toDomain($row))
            ->all();
    }

    public function listByClientId(int $clientId): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ORD_Order> $rows */
        $rows = ORD_Order::query()
            ->with('lines')
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return $rows
            ->map(fn (ORD_Order $row): Order => $this->mapper->toDomain($row))
            ->all();
    }

    public function save(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $payload = $this->mapper->toPersistence($order);
            $orderPayload = $payload['order'];
            $lines = $payload['lines'];

            if (! $order->hasId()) {
                unset($orderPayload['id']);
                $row = ORD_Order::query()->create($orderPayload);
                $order->assignId((int) $row->id);
            } else {
                $id = (int) $orderPayload['id'];
                unset($orderPayload['id']);
                ORD_Order::query()->whereKey($id)->update($orderPayload);
                ORD_OrderLine::query()->where('order_id', $id)->delete();
            }

            foreach ($lines as $line) {
                ORD_OrderLine::query()->create([
                    ...$line,
                    'order_id' => $order->id(),
                ]);
            }
        });
    }

    /**
     * @param  array{
     *     status?: string|null,
     *     q?: string|null,
     *     branch_id?: int|null,
     *     delivery_method?: string|null
     * }  $filters
     * @return Builder<ORD_Order>
     */
    private function filteredQuery(array $filters): Builder
    {
        /** @var Builder<ORD_Order> $query */
        $query = ORD_Order::query();

        $status = isset($filters['status']) ? trim((string) $filters['status']) : '';
        if ($status !== '') {
            $query->where('status', $status);
        }

        $branchId = isset($filters['branch_id']) ? (int) $filters['branch_id'] : 0;
        if ($branchId > 0) {
            $query->where('branch_id', $branchId);
        }

        $method = isset($filters['delivery_method']) ? trim((string) $filters['delivery_method']) : '';
        if ($method !== '') {
            $query->where('delivery_type', $method);
        }

        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $parsedNumber = OrderNumber::tryParse($q);
            $query->where(function (Builder $inner) use ($q, $parsedNumber): void {
                if ($parsedNumber !== null) {
                    $inner->orWhere('id', $parsedNumber['id']);

                    return;
                }
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }
                $like = '%'.$q.'%';
                $inner->orWhere('client_name', 'like', $like)
                    ->orWhere('client_phone', 'like', $like);
            });
        }

        return $query;
    }
}
