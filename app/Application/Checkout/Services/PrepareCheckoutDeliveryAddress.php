<?php

namespace App\Application\Checkout\Services;

use App\Domain\Checkout\ValueObject\DeliveryAddress;
use App\Domain\Delivery\Port\DeliveryAddressGeocoderPort;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Shared\Enum\DeliveryMethod;

/**
 * Геокодирование адреса курьера для расчёта тарифа in/out zone.
 */
final class PrepareCheckoutDeliveryAddress
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $deliveryConfigurations,
        private readonly DeliveryAddressGeocoderPort $geocoder,
    ) {}

    public function prepare(?DeliveryMethod $method, ?DeliveryAddress $address): ?DeliveryAddress
    {
        if ($method !== DeliveryMethod::Courier || ! $address instanceof DeliveryAddress) {
            return $address;
        }

        $latitude = $address->latitude();
        $longitude = $address->longitude();

        if ($latitude !== null && $longitude !== null) {
            return $address;
        }

        $configuration = $this->deliveryConfigurations->findPublic();
        $geocoded = $this->geocoder->geocode(
            street: $address->street(),
            house: $address->house(),
            city: $configuration?->kitchenAddress()->city(),
        );

        if ($geocoded === null) {
            return $address;
        }

        return new DeliveryAddress(
            street: $address->street(),
            house: $address->house(),
            entrance: $address->entrance(),
            apartment: $address->apartment(),
            latitude: $geocoded['latitude'],
            longitude: $geocoded['longitude'],
        );
    }
}
