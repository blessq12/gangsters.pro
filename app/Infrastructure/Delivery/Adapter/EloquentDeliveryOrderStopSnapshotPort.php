<?php

namespace App\Infrastructure\Delivery\Adapter;

use App\Domain\Core\Delivery\Routing\Port\DeliveryOrderStopSnapshotPort;
use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Repository\OrderRepository;
use App\Domain\Core\Order\ValueObject\OrderNumber;

/**
 * ACL: Delivery читает снимок заказа через OrderRepository в Infrastructure.
 */
final class EloquentDeliveryOrderStopSnapshotPort implements DeliveryOrderStopSnapshotPort
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function findMany(array $orderIds): array
    {
        $map = [];
        foreach ($orderIds as $orderId) {
            $id = (int) $orderId;
            if ($id < 1) {
                continue;
            }
            $order = $this->orders->findById($id);
            if ($order instanceof Order) {
                $map[$id] = $this->snapshot($order);
            }
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Order $order): array
    {
        $delivery = $order->delivery();
        $client = $order->client();
        $address = $delivery->address();
        $id = $order->hasId() ? $order->id() : 0;

        return [
            'id' => $id,
            'number' => $id > 0 ? OrderNumber::format($id, $order->createdAt()) : null,
            'status' => $order->status()->value,
            'method' => $delivery->type()->value,
            'address' => $address === null ? null : [
                'street' => $address->street(),
                'house' => $address->house(),
                'entrance' => $address->entrance(),
                'floor' => $address->floor(),
                'apartment' => $address->apartment(),
                'comment' => $address->comment(),
            ],
            'client' => [
                'name' => $client->name(),
                'phone' => $client->phone(),
            ],
        ];
    }
}
