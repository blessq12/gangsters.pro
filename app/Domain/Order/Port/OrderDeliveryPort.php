<?php

namespace App\Domain\Order\Port;

use App\Domain\Delivery\ValueObject\DeliveryZone;

/**
 * Чтение данных доставки при оформлении заказа.
 */
interface OrderDeliveryPort
{
    public function resolveZone(?float $latitude, ?float $longitude): ?DeliveryZone;

    public function resolveKitchenCity(): ?string;

    public function resolveMinOrderAmountKopecks(): ?int;
}
