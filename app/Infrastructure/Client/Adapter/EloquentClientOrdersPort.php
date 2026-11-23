<?php

namespace App\Infrastructure\Client\Adapter;

use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderNumber;
use App\Domain\Supporting\Client\Port\ClientOrdersPort;

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
}
