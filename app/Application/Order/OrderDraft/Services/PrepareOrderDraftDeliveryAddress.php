<?php

namespace App\Application\Order\OrderDraft\Services;

use App\Domain\Order\OrderDraft\ValueObject\DeliveryAddress;
use App\Domain\Content\Port\DeliveryAddressGeocoderPort;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Shared\Enum\DeliveryMethod;

/**
 * Геокодирование адреса курьера для расчёта тарифа in/out zone.
 */
final class PrepareOrderDraftDeliveryAddress
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

        $street = trim($address->street());
        $house = trim($address->house());

        if ($street !== '' && $house !== '') {
            $configuration = $this->deliveryConfigurations->findPublic();
            $geocoded = $this->geocoder->geocode(
                street: $street,
                house: $house,
                city: $configuration?->kitchenAddress()->city(),
            );

            if ($geocoded !== null) {
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

        $latitude = $address->latitude();
        $longitude = $address->longitude();

        if (self::coordinatesAreUsable($latitude, $longitude)) {
            return $address;
        }

        return $address;
    }

    private static function coordinatesAreUsable(?float $latitude, ?float $longitude): bool
    {
        if ($latitude === null || $longitude === null) {
            return false;
        }

        if (! is_finite($latitude) || ! is_finite($longitude)) {
            return false;
        }

        if (abs($latitude) < 1e-6 && abs($longitude) < 1e-6) {
            return false;
        }

        return abs($latitude) <= 90 && abs($longitude) <= 180;
    }
}
