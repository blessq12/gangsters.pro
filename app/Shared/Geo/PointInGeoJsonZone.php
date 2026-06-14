<?php

namespace App\Shared\Geo;

/**
 * Проверка точки в GeoJSON Polygon / MultiPolygon (координаты [lng, lat]).
 */
final class PointInGeoJsonZone
{
    /**
     * @param  array<string, mixed>|null  $geoJson
     */
    public static function contains(?array $geoJson, ?float $latitude, ?float $longitude): ?bool
    {
        if ($geoJson === null || $latitude === null || $longitude === null) {
            return null;
        }

        $type = $geoJson['type'] ?? null;

        if ($type === 'Polygon') {
            /** @var list<list<list<float>>> $coordinates */
            $coordinates = $geoJson['coordinates'] ?? [];

            return self::pointInPolygonCoordinates($longitude, $latitude, $coordinates);
        }

        if ($type === 'MultiPolygon') {
            /** @var list<list<list<list<float>>>> $polygons */
            $polygons = $geoJson['coordinates'] ?? [];

            foreach ($polygons as $polygonCoordinates) {
                if (self::pointInPolygonCoordinates($longitude, $latitude, $polygonCoordinates)) {
                    return true;
                }
            }

            return $polygons !== [] ? false : null;
        }

        return null;
    }

    /**
     * @param  list<list<list<float>>>  $polygonCoordinates
     */
    private static function pointInPolygonCoordinates(float $longitude, float $latitude, array $polygonCoordinates): bool
    {
        if ($polygonCoordinates === []) {
            return false;
        }

        $outerRing = $polygonCoordinates[0] ?? [];
        if (! self::pointInRing($longitude, $latitude, $outerRing)) {
            return false;
        }

        for ($index = 1, $count = count($polygonCoordinates); $index < $count; $index++) {
            if (self::pointInRing($longitude, $latitude, $polygonCoordinates[$index])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<list<float>>  $ring
     */
    private static function pointInRing(float $longitude, float $latitude, array $ring): bool
    {
        $vertexCount = count($ring);
        if ($vertexCount < 3) {
            return false;
        }

        $inside = false;

        for ($index = 0, $previous = $vertexCount - 1; $index < $vertexCount; $previous = $index++) {
            $xi = (float) ($ring[$index][0] ?? 0);
            $yi = (float) ($ring[$index][1] ?? 0);
            $xj = (float) ($ring[$previous][0] ?? 0);
            $yj = (float) ($ring[$previous][1] ?? 0);

            $intersects = ($yi > $latitude) !== ($yj > $latitude)
                && $longitude < (($xj - $xi) * ($latitude - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
