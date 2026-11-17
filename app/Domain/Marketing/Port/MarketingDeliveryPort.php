<?php

namespace App\Domain\Marketing\Port;

/**
 * Чтение условий доставки для витрины: только массивы, без сущностей Delivery.
 *
 * Снимок конфигурации: {id, min_order_amount_kopecks, average_delivery_time_minutes, min_delivery_fee_kopecks, zones}
 */
interface MarketingDeliveryPort
{
    /**
     * @return array<string, mixed>|null
     */
    public function findSnapshotById(int $configurationId): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listActive(): array;
}
