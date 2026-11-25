<?php

namespace Tests\Application\Core\Support;

use App\Domain\Core\Delivery\Routing\Port\DeliveryRouteOrderEligibilityPort;

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
