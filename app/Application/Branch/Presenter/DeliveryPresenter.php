<?php

namespace App\Application\Branch\Presenter;

use App\Domain\Supporting\Branch\Entity\Branch;
use App\Domain\Supporting\Branch\Entity\DeliveryConfiguration;
use App\Domain\Supporting\Branch\ValueObject\DeliveryZone;

/**
 * Единый формат ответа Delivery для витрины и админки.
 * Адрес филиала — снимок порта, не entity Company.
 */
final class DeliveryPresenter
{
    /**
     * @param  array<string, mixed>|null  $branchAddress
     * @return array{
     *     settings: array<string, mixed>,
     *     zones: list<array<string, mixed>>,
     *     zone_geojson: array<string, mixed>|null,
     *     branch_id: int|null,
     *     branch_address: array<string, mixed>|null
     * }
     */
    public function present(DeliveryConfiguration $configuration, ?array $branchAddress = null): array
    {
        return [
            'settings' => $this->settings($configuration),
            'zones' => array_map(
                fn (DeliveryZone $zone): array => $this->zone($zone),
                $configuration->zones(),
            ),
            'zone_geojson' => $configuration->deliveryZoneGeoJson(),
            'branch_id' => $configuration->branchId(),
            'branch_address' => $branchAddress,
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
     * @return array<string, mixed>|null
     */
    public function addressFromBranch(?Branch $branch): ?array
    {
        if (! $branch instanceof Branch) {
            return null;
        }

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
