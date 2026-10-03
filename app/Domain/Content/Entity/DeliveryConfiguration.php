<?php

namespace App\Domain\Content\Entity;

use App\Domain\Content\ValueObject\DeliveryZone;
use App\Domain\Content\ValueObject\KitchenAddress;

/**
 * Публичная конфигурация доставки: кухня и список зон.
 */
final class DeliveryConfiguration
{
    /**
     * @param  list<DeliveryZone>  $zones
     */
    public function __construct(
        private readonly int $id,
        private readonly ?int $minOrderAmountKopecks,
        private readonly ?int $averageDeliveryTimeMinutes,
        private readonly KitchenAddress $kitchenAddress,
        private readonly ?float $kitchenLatitude,
        private readonly ?float $kitchenLongitude,
        private readonly array $zones,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function minOrderAmountKopecks(): ?int
    {
        return $this->minOrderAmountKopecks;
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
     * @return list<DeliveryZone>
     */
    public function zones(): array
    {
        return $this->zones;
    }

    /**
     * Минимальный тариф среди зон — для публичного «доставка от».
     */
    public function minDeliveryFeeKopecks(): ?int
    {
        if ($this->zones === []) {
            return null;
        }

        $fees = array_map(
            static fn (DeliveryZone $zone): int => $zone->deliveryFeeKopecks(),
            $this->zones,
        );

        return min($fees);
    }

    /**
     * Объединённый MultiPolygon всех зон (для карт / legacy).
     *
     * @return array<string, mixed>|null
     */
    public function deliveryZoneGeoJson(): ?array
    {
        $polygons = [];

        foreach ($this->zones as $zone) {
            $geometry = $zone->geometry();
            $type = $geometry['type'] ?? null;

            if ($type === 'Polygon') {
                $polygons[] = $geometry['coordinates'] ?? [];
            } elseif ($type === 'MultiPolygon') {
                foreach ($geometry['coordinates'] ?? [] as $polygonCoordinates) {
                    $polygons[] = $polygonCoordinates;
                }
            }
        }

        if ($polygons === []) {
            return null;
        }

        if (count($polygons) === 1) {
            return [
                'type' => 'Polygon',
                'coordinates' => $polygons[0],
            ];
        }

        return [
            'type' => 'MultiPolygon',
            'coordinates' => $polygons,
        ];
    }
}
