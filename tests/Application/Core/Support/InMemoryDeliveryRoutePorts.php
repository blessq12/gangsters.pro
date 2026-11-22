<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Delivery\Routing\Port\DeliveryOrderStopSnapshotPort;
use App\Domain\Core\Delivery\Routing\Port\DeliveryRouteOrderEligibilityPort;
use App\Domain\Core\Order\ValueObject\OrderNumber;

final class InMemoryDeliveryRouteOrderEligibilityPort implements DeliveryRouteOrderEligibilityPort
{
    public function __construct(
        private readonly InMemoryOrderRepository $orders,
    ) {}

    public function resolve(array $orderIds): array
    {
        $rows = [];
        foreach ($orderIds as $rawId) {
            $orderId = (int) $rawId;
            $order = $this->orders->findById($orderId);
            if ($order === null) {
                throw new \InvalidArgumentException(sprintf('Заказ #%d не найден.', $orderId));
            }
            $branchId = $order->branchId();
            if ($branchId === null || $branchId < 1) {
                throw new \InvalidArgumentException(sprintf('У заказа #%d нет филиала.', $orderId));
            }
            $rows[] = [
                'order_id' => $orderId,
                'branch_id' => $branchId,
                'assignable' => $order->canBeAssignedToRoute(),
            ];
        }

        return $rows;
    }
}

final class InMemoryDeliveryOrderStopSnapshotPort implements DeliveryOrderStopSnapshotPort
{
    public function __construct(
        private readonly InMemoryOrderRepository $orders,
    ) {}

    public function findMany(array $orderIds): array
    {
        $map = [];
        foreach ($orderIds as $rawId) {
            $orderId = (int) $rawId;
            $order = $this->orders->findById($orderId);
            if ($order === null) {
                continue;
            }
            $delivery = $order->delivery();
            $address = $delivery->address();
            $client = $order->client();
            $id = $order->hasId() ? $order->id() : 0;
            $map[$orderId] = [
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

        return $map;
    }
}
