<?php

namespace App\Application\Delivery\Presenter;

use App\Domain\Company\Entity\Kitchen;
use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\ValueObject\DeliveryZone;

/**
 * Единый формат ответа Delivery для витрины и админки.
 * Адрес кухни собирается из Company при необходимости.
 */
final class DeliveryPresenter
{
    /**
     * @return array{
     *     settings: array<string, mixed>,
     *     zones: list<array<string, mixed>>,
     *     zone_geojson: array<string, mixed>|null,
     *     kitchen_id: int|null,
     *     kitchen_address: array<string, mixed>|null
     * }
     */
    public function present(DeliveryConfiguration $configuration, ?Kitchen $kitchen = null): array
    {
        return [
            'settings' => $this->settings($configuration),
            'zones' => array_map(
                fn (DeliveryZone $zone): array => $this->zone($zone),
                $configuration->zones(),
            ),
            'zone_geojson' => $configuration->deliveryZoneGeoJson(),
            'kitchen_id' => $configuration->kitchenId(),
            'kitchen_address' => $kitchen instanceof Kitchen
                ? $this->kitchenFromCompany($kitchen)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(DeliveryConfiguration $configuration): array
    {
        return [
            'min_order_amount_kopecks' => $configuration->minOrderAmountKopecks(),
            'min_order_rubles' => $configuration->minOrderAmountKopecks() === null
                ? null
                : intdiv($configuration->minOrderAmountKopecks(), 100),
            'delivery_fee_kopecks' => $configuration->minDeliveryFeeKopecks(),
            'average_delivery_time_minutes' => $configuration->averageDeliveryTimeMinutes(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function zone(DeliveryZone $zone): array
    {
        return [
            'id' => $zone->id(),
            'name' => $zone->name(),
            'fee' => intdiv($zone->deliveryFeeKopecks(), 100),
            'fee_rubles' => intdiv($zone->deliveryFeeKopecks(), 100),
            'delivery_fee_kopecks' => $zone->deliveryFeeKopecks(),
            'is_remote' => $zone->isRemote(),
            'delivery_product_id' => $zone->deliveryProductId(),
            'geometry' => $zone->geometry(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function kitchenFromCompany(Kitchen $kitchen): array
    {
        $parts = array_values(array_filter([
            $kitchen->city(),
            $kitchen->street(),
            $kitchen->house(),
        ], static fn (?string $part): bool => $part !== null && trim($part) !== ''));

        return [
            'city' => $kitchen->city(),
            'street' => $kitchen->street(),
            'house' => $kitchen->house(),
            'comment' => $kitchen->comment(),
            'search_line' => $parts !== [] ? implode(', ', $parts) : null,
            'latitude' => $kitchen->latitude(),
            'longitude' => $kitchen->longitude(),
        ];
    }
}
