<?php

namespace App\Domain\Promotion\ValueObject;

use App\Domain\Promotion\Enum\DeliveryFeeMode;

/**
 * Политика стоимости доставки от суммы корзины и зоны (при courier).
 */
final class DeliveryBenefitPolicy
{
    public function __construct(
        private readonly int $freeDeliveryThresholdKopecks,
        private readonly int $outsideZoneSurchargeKopecks,
        private readonly DeliveryFeeMode $belowThresholdFeeMode,
        private readonly DeliveryFeeMode $inZoneAtThresholdFeeMode,
        private readonly DeliveryFeeMode $outsideZoneAtThresholdFeeMode,
        private readonly bool $isActive,
    ) {
        if ($freeDeliveryThresholdKopecks < 0 || $outsideZoneSurchargeKopecks < 0) {
            throw new \InvalidArgumentException('Порог и надбавка доставки не могут быть отрицательными.');
        }
    }

    public function freeDeliveryThresholdKopecks(): int
    {
        return $this->freeDeliveryThresholdKopecks;
    }

    public function outsideZoneSurchargeKopecks(): int
    {
        return $this->outsideZoneSurchargeKopecks;
    }

    public function belowThresholdFeeMode(): DeliveryFeeMode
    {
        return $this->belowThresholdFeeMode;
    }

    public function inZoneAtThresholdFeeMode(): DeliveryFeeMode
    {
        return $this->inZoneAtThresholdFeeMode;
    }

    public function outsideZoneAtThresholdFeeMode(): DeliveryFeeMode
    {
        return $this->outsideZoneAtThresholdFeeMode;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
