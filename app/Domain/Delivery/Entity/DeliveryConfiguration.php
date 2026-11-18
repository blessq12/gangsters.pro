<?php

namespace App\Domain\Delivery\Entity;

use App\Domain\Delivery\ValueObject\DeliveryZone;

/**
 * Публичная конфигурация доставки: ссылка на кухню Company и список зон.
 * Адрес/координаты кухни здесь не хранятся — только kitchenId.
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
        private readonly ?int $kitchenId,
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

    public function kitchenId(): ?int
    {
        return $this->kitchenId;
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
