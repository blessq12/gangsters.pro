<?php

namespace App\Infrastructure\Delivery\Port;

use App\Domain\Delivery\Port\DeliveryAddressGeocoderPort;
use Illuminate\Support\Facades\Http;

final class YandexDeliveryAddressGeocoderAdapter implements DeliveryAddressGeocoderPort
{
    public function geocode(string $street, string $house, ?string $city): ?array
    {
        $street = trim($street);
        $house = trim($house);

        if ($street === '' || $house === '') {
            return null;
        }

        $apiKey = config('services.yandex_maps.geocoder_api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            return null;
        }

        $queryParts = array_filter([
            is_string($city) ? trim($city) : null,
            $street,
            'д. '.$house,
        ]);

        $response = Http::timeout(5)->get('https://geocode-maps.yandex.ru/1.x/', [
            'apikey' => $apiKey,
            'geocode' => implode(', ', $queryParts),
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
