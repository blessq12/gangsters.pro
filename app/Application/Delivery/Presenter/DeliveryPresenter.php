<?php

namespace App\Application\Delivery\Presenter;

use App\Domain\Core\Delivery\Tariff\Entity\DeliveryConfiguration;
use App\Domain\Core\Delivery\Tariff\ValueObject\DeliveryZone;
use App\Domain\Supporting\Company\Entity\Branch;

/**
 * Единый формат ответа Delivery для витрины и админки.
 * Адрес филиала собирается из Company при необходимости.
 */
final class DeliveryPresenter
{
    /**
     * @return array{
     *     settings: array<string, mixed>,
     *     zones: list<array<string, mixed>>,
     *     zone_geojson: array<string, mixed>|null,
     *     branch_id: int|null,
     *     branch_address: array<string, mixed>|null
     * }
     */
    public function present(DeliveryConfiguration $configuration, ?Branch $branch = null): array
    {
        return [
            'settings' => $this->settings($configuration),
            'zones' => array_map(
                fn (DeliveryZone $zone): array => $this->zone($zone),
                $configuration->zones(),
            ),
            'zone_geojson' => $configuration->deliveryZoneGeoJson(),
            'branch_id' => $configuration->branchId(),
            'branch_address' => $branch instanceof Branch
                ? $this->branchAddress($branch)
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
    public function branchAddress(Branch $branch): array
    {
        $parts = array_values(array_filter([
            $branch->city(),
            $branch->street(),
            $branch->house(),
        ], static fn (?string $part): bool => $part !== null && trim($part) !== ''));

        return [
            'city' => $branch->city(),
            'street' => $branch->street(),
            'house' => $branch->house(),
            'comment' => $branch->comment(),
            'search_line' => $parts !== [] ? implode(', ', $parts) : null,
            'latitude' => $branch->latitude(),
            'longitude' => $branch->longitude(),
        ];
    }
}
