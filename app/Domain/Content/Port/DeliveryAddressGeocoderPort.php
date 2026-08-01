<?php

namespace App\Domain\Content\Port;

interface DeliveryAddressGeocoderPort
{
    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $street, string $house, ?string $city): ?array;
}
