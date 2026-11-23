<?php

namespace App\Domain\Core\Delivery\Routing\Port;

/**
 * Снимок заказа для отображения остановки маршрута.
 * Delivery Domain не импортирует Order.
 *
 * @phpstan-type StopOrderSnapshot array{
 *     id: int,
 *     number: string|null,
 *     status: string,
 *     method: string,
 *     address: array{
 *         street: string,
 *         house: string,
 *         entrance: string|null,
 *         floor: string|null,
 *         apartment: string|null,
 *         comment: string|null
 *     }|null,
 *     client: array{name: string|null, phone: string|null}
 * }
 */
interface DeliveryOrderStopSnapshotPort
{
    /**
     * @param  list<int>  $orderIds
     * @return array<int, StopOrderSnapshot> map orderId → snapshot
     */
    public function findMany(array $orderIds): array;
}
