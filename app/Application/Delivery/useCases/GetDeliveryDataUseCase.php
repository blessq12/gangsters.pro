<?php

namespace App\Application\Delivery\useCases;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Delivery\ValueObject\KitchenAddress;

/**
 * Сценарий: получить публичные данные доставки.
 */
final class GetDeliveryDataUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $configuration,
    ) {}

    /**
     * @return array{data: array<string, mixed>|null}
     */
    public function execute(): array
    {
        $config = $this->configuration->findPublic();

        return [
            'data' => $config instanceof DeliveryConfiguration
                ? $this->mapConfiguration($config)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapConfiguration(DeliveryConfiguration $config): array
    {
        $address = $config->kitchenAddress();

        return [
            'settings' => [
                'min_order_amount_kopecks' => $config->minOrderAmountKopecks(),
                'delivery_fee_kopecks' => $config->deliveryFeeKopecks(),
                'outside_zone_delivery_fee_kopecks' => $config->outsideZoneDeliveryFeeKopecks(),
                'average_delivery_time_minutes' => $config->averageDeliveryTimeMinutes(),
            ],
            'zone' => [
                'kitchen_address' => $this->mapKitchenAddress($address),
                'kitchen_latitude' => $config->kitchenLatitude(),
                'kitchen_longitude' => $config->kitchenLongitude(),
                'delivery_zone_geojson' => $config->deliveryZoneGeoJson(),
            ],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function mapKitchenAddress(KitchenAddress $address): array
    {
        return [
            'city' => $address->city(),
            'street' => $address->street(),
            'house' => $address->house(),
            'comment' => $address->comment(),
            'search_line' => $address->searchLine(),
        ];
    }
}
