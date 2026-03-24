<?php

namespace App\Infrastructure\Client\Query;

use App\Application\Client\Query\ClientSummaryReader;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Order\Model\ORD_Order;
use DateTimeImmutable;

final class EloquentClientSummaryReader implements ClientSummaryReader
{
    public function getSummaryById(int $clientId): ?array
    {
        $clientModel = UR_Client::withCount([
            'addresses' => fn ($query) => $query->whereNull('deleted_at'),
        ])->find($clientId);

        if ($clientModel === null) {
            return null;
        }

        $ordersQuery = ORD_Order::query()->where('client_id', $clientId);
        $ordersCount = (int) $ordersQuery->count();
        $paidOrdersCount = (int) ORD_Order::query()
            ->where('client_id', $clientId)
            ->where('payment_status', 'paid')
            ->count();
        $ordersTotal = (int) ORD_Order::query()
            ->where('client_id', $clientId)
            ->sum('total');
        $lastOrderAt = ORD_Order::query()
            ->where('client_id', $clientId)
            ->latest('created_at')
            ->value('created_at');

        return [
            'client_id' => (int) $clientModel->id,
            'orders_count' => $ordersCount,
            'paid_orders_count' => $paidOrdersCount,
            'orders_total' => $ordersTotal,
            'average_order_total' => $ordersCount > 0 ? (int) floor($ordersTotal / $ordersCount) : 0,
            'last_order_at' => $lastOrderAt ? (new DateTimeImmutable((string) $lastOrderAt))->format(DATE_ATOM) : null,
            'addresses_count' => (int) ($clientModel->addresses_count ?? 0),
        ];
    }
}

