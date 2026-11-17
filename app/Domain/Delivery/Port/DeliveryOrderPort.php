<?php

namespace App\Domain\Delivery\Port;

/**
 * Данные доставки заказа для Delivery: только чтение, массивы без сущностей Order.
 *
 * Снимок доставки: {order_id, address, latitude, longitude, comment}
 */
interface DeliveryOrderPort
{
    /**
     * @return array<string, mixed>|null
     */
    public function findDeliverySnapshot(int $orderId): ?array;

    /** Заказ готов к передаче курьеру. */
    public function isReadyForDelivery(int $orderId): bool;
}
