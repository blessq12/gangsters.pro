<?php

namespace App\Infrastructure\Shared\Geo;

use App\Shared\Geo\AddressGeocoder;
use Illuminate\Support\Facades\Http;

final class YandexAddressGeocoder implements AddressGeocoder
{
    public function geocode(string $street, string $house, ?string $city = null): ?array
    {
        $street = trim($street);
        $house = trim($house);

        if ($street === '' || $house === '') {
            return null;
        }

        $city = is_string($city) ? trim($city) : null;
        if ($city === '') {
            $city = null;
        }

        $queryParts = array_filter([
            $city,
            $street,
            'д. '.$house,
        ]);

        return $this->request(implode(', ', $queryParts));
    }

    public function geocodeQuery(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        return $this->request($query);
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function request(string $geocode): ?array
    {
        $apiKey = config('services.yandex_maps.geocoder_api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            return null;
        }

        $response = Http::timeout(5)->get('https://geocode-maps.yandex.ru/1.x/', [
            'apikey' => $apiKey,
            'geocode' => $geocode,
            'format' => 'json',
            'lang' => 'ru_RU',
            'results' => 1,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $position = $response->json('response.GeoObjectCollection.featureMember.0.GeoObject.Point.pos');
        if (! is_string($position) || trim($position) === '') {
            return null;
        }

        $parts = preg_split('/\s+/', trim($position)) ?: [];
        if (count($parts) < 2) {
            return null;
        }

        $longitude = (float) $parts[0];
        $latitude = (float) $parts[1];

        if (! is_finite($latitude) || ! is_finite($longitude)) {
            return null;
        }

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }
}
