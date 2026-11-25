<?php

namespace App\Infrastructure\Client\Adapter;

use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderNumber;
use App\Domain\Supporting\Client\Port\ClientOrdersPort;
use App\Infrastructure\Order\Model\OrderModel;
use DateTimeImmutable;

/**
 * ACL: Client читает историю заказов через OrderRepository в Infrastructure.
 */
final class EloquentClientOrdersPort implements ClientOrdersPort
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function listByClientId(int $clientId): array
    {
        $rows = [];
        foreach ($this->orders->listByClientId($clientId) as $order) {
            $id = $order->hasId() ? $order->id() : 0;
            $rows[] = [
                'id' => $id,
                'number' => $id > 0 ? OrderNumber::format($id, $order->createdAt()) : null,
                'status' => $order->status()->value,
                'created_at' => $order->createdAt()->format(DATE_ATOM),
                'grand_total_kopecks' => $order->grandTotalKopecks(),
            ];
        }

        return $rows;
    }

    public function summariesByClientIds(array $clientIds): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $clientIds),
            static fn (int $id): bool => $id > 0,
        )));

        if ($ids === []) {
            return [];
        }

        /** @var \Illuminate\Support\Collection<int, object{client_id: mixed, orders_count: mixed, last_order_at: mixed}> $rows */
        $rows = OrderModel::query()
            ->selectRaw('client_id, COUNT(*) as orders_count, MAX(created_at) as last_order_at')
            ->whereIn('client_id', $ids)
            ->groupBy('client_id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $clientId = (int) $row->client_id;
            $lastRaw = $row->last_order_at;
            $lastAt = null;
            if ($lastRaw !== null && $lastRaw !== '') {
                try {
                    $lastAt = (new DateTimeImmutable((string) $lastRaw))->format(DATE_ATOM);
                } catch (\Exception) {
                    $lastAt = null;
                }
            }
            $result[$clientId] = [
                'orders_count' => (int) $row->orders_count,
                'last_order_at' => $lastAt,
            ];
        }

        return $result;
    }
}
