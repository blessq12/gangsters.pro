<?php

namespace App\Shared\Geo;

/**
 * Геокодирование адреса доставки → координаты.
 * Shared: не принадлежит ни одному BC.
 */
interface AddressGeocoder
{
    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $street, string $house, ?string $city = null): ?array;
}
