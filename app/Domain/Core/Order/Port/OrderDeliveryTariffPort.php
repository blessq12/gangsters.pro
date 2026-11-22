<?php

namespace App\Domain\Core\Order\Port;

/**
 * Снимок тарифов доставки для оформления заказа. Order Domain не импортирует Delivery Tariff.
 *
 * @phpstan-type OrderDeliveryZoneSnapshot array{
 *     id: string,
 *     name: string,
 *     fee_kopecks: int,
 *     is_remote: bool
 * }
 * @phpstan-type OrderDeliveryConfigSnapshot array{
 *     min_order_kopecks: int|null,
 *     zones: list<OrderDeliveryZoneSnapshot>
 * }
 */
interface OrderDeliveryTariffPort
{
    /**
     * @return OrderDeliveryConfigSnapshot|null
     */
    public function findConfigByBranch(int $branchId): ?array;

    /**
     * Первая зона, в которую попадает точка (как DeliveryZoneMatcher).
     *
     * @return OrderDeliveryZoneSnapshot|null
     */
    public function matchZoneByCoordinates(int $branchId, ?float $latitude, ?float $longitude): ?array;

    /**
     * Зона по id в конфиге филиала.
     *
     * @return OrderDeliveryZoneSnapshot|null
     */
    public function findZoneById(int $branchId, string $zoneId): ?array;
}
