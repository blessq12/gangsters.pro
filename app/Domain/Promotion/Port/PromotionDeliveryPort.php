<?php

namespace App\Domain\Promotion\Port;

use App\Domain\Delivery\ValueObject\DeliveryZone;

/**
 * Входные данные доставки для акций: зона и порог бесплатной доставки.
 * Promotion зонами не владеет — только читает их через порт.
 */
interface PromotionDeliveryPort
{
    public function resolveZone(?float $latitude, ?float $longitude): ?DeliveryZone;

    public function resolveFreeDeliveryThresholdKopecks(): ?int;
}
