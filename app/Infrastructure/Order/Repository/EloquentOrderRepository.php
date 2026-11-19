<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Order\Model\ORD_OrderLine;
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

    public function count(?string $status = null): int
    {
        $query = ORD_Order::query();
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->count();
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

    public function listPage(int $offset, int $limit, ?string $status = null): array
    {
        $query = ORD_Order::query()->with('lines')->orderByDesc('id');
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, ORD_Order> $rows */
        $rows = $query
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
}
