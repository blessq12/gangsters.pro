<?php

namespace App\Support\Maps;

/**
 * GeoJSON [longitude, latitude] ↔ Yandex Maps [latitude, longitude].
 * Mirror of public/js/maps/yandexGeoJsonCoords.js for server-side tests.
 */
final class YandexGeoJsonCoords
{
    public static function geoJsonPositionToYmaps(float|int $lon, float|int $lat): ?array
    {
        if (self::looksLikeLongitude($lon) && self::looksLikeLatitude($lat)) {
            return [$lat, $lon];
        }

        if (self::looksLikeLatitude($lon) && self::looksLikeLongitude($lat)) {
            return [$lon, $lat];
        }

        return null;
    }

    public static function ymapsPositionToGeoJson(float|int $lat, float|int $lng): ?array
    {
        if (! self::looksLikeLatitude($lat) || ! self::looksLikeLongitude($lng)) {
            return null;
        }

        return [$lng, $lat];
    }

    private static function looksLikeLatitude(float|int $value): bool
    {
        return $value >= 41 && $value <= 82;
    }

    private static function looksLikeLongitude(float|int $value): bool
    {
        return $value >= 19 && $value <= 180;
    }
}
