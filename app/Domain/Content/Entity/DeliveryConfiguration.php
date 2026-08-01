<?php

namespace App\Domain\Content\Entity;

use App\Domain\Content\ValueObject\KitchenAddress;

/**
 * Публичная конфигурация доставки: тарифы и зона.
 */
final class DeliveryConfiguration
{
    /**
     * @param  array<string, mixed>|null  $deliveryZoneGeoJson
     */
    public function __construct(
        private readonly int $id,
        private readonly ?int $minOrderAmountKopecks,
        private readonly ?int $deliveryFeeKopecks,
        private readonly ?int $outsideZoneDeliveryFeeKopecks,
        private readonly ?int $averageDeliveryTimeMinutes,
        private readonly KitchenAddress $kitchenAddress,
        private readonly ?float $kitchenLatitude,
        private readonly ?float $kitchenLongitude,
        private readonly ?array $deliveryZoneGeoJson,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function minOrderAmountKopecks(): ?int
    {
        return $this->minOrderAmountKopecks;
    }

    public function deliveryFeeKopecks(): ?int
    {
        return $this->deliveryFeeKopecks;
    }

    public function outsideZoneDeliveryFeeKopecks(): ?int
    {
        return $this->outsideZoneDeliveryFeeKopecks;
    }

    public function averageDeliveryTimeMinutes(): ?int
    {
        return $this->averageDeliveryTimeMinutes;
    }

    public function kitchenAddress(): KitchenAddress
    {
        return $this->kitchenAddress;
    }

    public function kitchenLatitude(): ?float
    {
        return $this->kitchenLatitude;
    }

    public function kitchenLongitude(): ?float
    {
        return $this->kitchenLongitude;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function deliveryZoneGeoJson(): ?array
    {
        return $this->deliveryZoneGeoJson;
    }
}
