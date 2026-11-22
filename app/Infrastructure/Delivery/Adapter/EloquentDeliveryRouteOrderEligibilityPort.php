<?php

namespace App\Infrastructure\Delivery\Adapter;

use App\Domain\Core\Delivery\Routing\Port\DeliveryRouteOrderEligibilityPort;
use App\Domain\Core\Order\Repository\OrderRepository;

/**
 * ACL: eligibility для маршрутного листа без Order в Delivery Application.
 */
final class EloquentDeliveryRouteOrderEligibilityPort implements DeliveryRouteOrderEligibilityPort
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function resolve(array $orderIds): array
    {
        $rows = [];
        foreach ($orderIds as $rawId) {
            $orderId = (int) $rawId;
            if ($orderId < 1) {
                throw new \InvalidArgumentException('Некорректный order_id.');
            }

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
