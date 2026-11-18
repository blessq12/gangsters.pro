<?php

namespace App\Application\Delivery\Presenter;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\ValueObject\DeliveryZone;
use App\Domain\Delivery\ValueObject\KitchenAddress;

/**
 * Единый формат ответа Delivery для витрины и админки.
 */
final class DeliveryPresenter
{
    /**
     * @return array{
     *     settings: array<string, mixed>,
     *     zones: list<array<string, mixed>>,
     *     zone_geojson: array<string, mixed>|null,
     *     kitchen_address: array<string, mixed>
     * }
     */
    public function present(DeliveryConfiguration $configuration): array
    {
        return [
            'settings' => $this->settings($configuration),
            'zones' => array_map(
                fn (DeliveryZone $zone): array => $this->zone($zone),
                $configuration->zones(),
            ),
            'zone_geojson' => $configuration->deliveryZoneGeoJson(),
            'kitchen_address' => $this->kitchenAddress(
                $configuration->kitchenAddress(),
                $configuration->kitchenLatitude(),
                $configuration->kitchenLongitude(),
            ),
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
    public function kitchenAddress(
        KitchenAddress $address,
        ?float $latitude,
        ?float $longitude,
    ): array {
        return [
            'city' => $address->city(),
            'street' => $address->street(),
            'house' => $address->house(),
            'comment' => $address->comment(),
            'search_line' => $address->searchLine(),
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }
}
