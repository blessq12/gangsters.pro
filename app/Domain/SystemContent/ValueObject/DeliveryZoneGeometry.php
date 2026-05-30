<?php

namespace App\Domain\SystemContent\ValueObject;

use InvalidArgumentException;

/**
 * GeoJSON geometry: Polygon or MultiPolygon (RFC 7946).
 */
final class DeliveryZoneGeometry
{
    private const ALLOWED_TYPES = ['Polygon', 'MultiPolygon'];

    private const COORD_EPSILON = 0.000001;

    private const COORD_PRECISION = 6;

    /**
     * @param  array{type: string, coordinates: array<mixed>}  $geometry
     */
    private function __construct(
        private readonly array $geometry,
    ) {}

    /**
     * @param  mixed  $value  array geometry, Feature, JSON string, or null
     */
    public static function fromMixed(mixed $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (! is_array($decoded)) {
                throw new InvalidArgumentException('Зона доставки: невалидный JSON.');
            }
            $value = $decoded;
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException('Зона доставки: ожидается объект GeoJSON.');
        }

        if (($value['type'] ?? '') === 'Feature') {
            $value = $value['geometry'] ?? null;
        }

        if (! is_array($value) || ! isset($value['type'], $value['coordinates'])) {
            throw new InvalidArgumentException('Зона доставки: ожидается geometry с type и coordinates.');
        }

        $type = (string) $value['type'];
        if (! in_array($type, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException('Зона доставки: допустимы только Polygon и MultiPolygon.');
        }

        $coordinates = self::normalizeCoordinates($type, $value['coordinates']);

        self::validateCoordinates($type, $coordinates);

        return new self([
            'type' => $type,
            'coordinates' => $coordinates,
        ]);
    }

    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * @return array{type: string, coordinates: array<mixed>}
     */
    public function toArray(): array
    {
        return $this->geometry;
    }

    /**
     * @return array{type: string, coordinates: array<mixed>}
     */
    public function toStorage(): array
    {
        return $this->toArray();
    }

    /**
     * @param  array<mixed>  $coordinates
     * @return array<mixed>
     */
    private static function normalizeCoordinates(string $type, array $coordinates): array
    {
        if ($type === 'Polygon') {
            return self::normalizePolygonRings($coordinates);
        }

        $normalized = [];
        foreach ($coordinates as $polygon) {
            if (! is_array($polygon)) {
                continue;
            }
            $normalized[] = self::normalizePolygonRings($polygon);
        }

        return $normalized;
    }

    /**
     * @param  array<mixed>  $rings
     * @return array<mixed>
     */
    private static function normalizePolygonRings(array $rings): array
    {
        if ($rings === [] || ! is_array($rings[0] ?? null)) {
            return $rings;
        }

        $rings[0] = self::normalizeOuterRing($rings[0]);

        return $rings;
    }

    /**
     * @param  array<mixed>  $outer
     * @return array<int, array{0: float, 1: float}>
     */
    private static function normalizeOuterRing(array $outer): array
    {
        $normalized = [];

        foreach ($outer as $position) {
            if (! is_array($position) || count($position) < 2) {
                continue;
            }

            if (! is_numeric($position[0]) || ! is_numeric($position[1])) {
                continue;
            }

            [$lon, $lat] = self::normalizeGeoJsonPositionPair(
                (float) $position[0],
                (float) $position[1],
            );

            $normalized[] = [
                round($lon, self::COORD_PRECISION),
                round($lat, self::COORD_PRECISION),
            ];
        }

        if ($normalized === []) {
            return $normalized;
        }

        $first = $normalized[0];
        $last = $normalized[count($normalized) - 1];

        if (count($normalized) > 3 && self::positionsEqual($first, $last)) {
            array_pop($normalized);
        }

        $normalized[] = $first;

        return $normalized;
    }

    /**
     * @param  array<mixed>  $coordinates
     */
    private static function validateCoordinates(string $type, array $coordinates): void
    {
        if ($type === 'Polygon') {
            self::validatePolygonRings($coordinates);

            return;
        }

        if ($coordinates === []) {
            throw new InvalidArgumentException('MultiPolygon не может быть пустым.');
        }

        foreach ($coordinates as $polygon) {
            if (! is_array($polygon)) {
                throw new InvalidArgumentException('MultiPolygon: невалидный полигон.');
            }
            self::validatePolygonRings($polygon);
        }
    }

    /**
     * @param  array<mixed>  $rings
     */
    private static function validatePolygonRings(array $rings): void
    {
        if ($rings === [] || ! is_array($rings[0] ?? null)) {
            throw new InvalidArgumentException('Polygon: требуется хотя бы одно кольцо координат.');
        }

        $outer = $rings[0];
        if (count($outer) < 4) {
            throw new InvalidArgumentException('Polygon: внешнее кольцо должно содержать минимум 4 точки (замкнутый контур).');
        }

        $first = $outer[0];
        $last = $outer[count($outer) - 1];
        if (! self::positionsEqual($first, $last)) {
            throw new InvalidArgumentException('Polygon: внешнее кольцо должно быть замкнутым.');
        }

        foreach ($outer as $position) {
            self::validatePosition($position);
        }
    }

    private static function validatePosition(mixed $position): void
    {
        if (! is_array($position) || count($position) < 2) {
            throw new InvalidArgumentException('Координаты: ожидается [долгота, широта].');
        }

        $lon = $position[0];
        $lat = $position[1];

        if (! is_numeric($lon) || ! is_numeric($lat)) {
            throw new InvalidArgumentException('Координаты должны быть числами.');
        }

        $lonF = (float) $lon;
        $latF = (float) $lat;

        if ($lonF < -180 || $lonF > 180 || $latF < -90 || $latF > 90) {
            throw new InvalidArgumentException('Координаты вне допустимого диапазона.');
        }
    }

    /**
     * @return array{0: float, 1: float} [longitude, latitude]
     */
    private static function normalizeGeoJsonPositionPair(float $first, float $second): array
    {
        if (self::looksLikeLatitude($first) && self::looksLikeLongitude($second)) {
            return [$second, $first];
        }

        return [$first, $second];
    }

    private static function looksLikeLatitude(float $value): bool
    {
        return $value >= 41.0 && $value <= 82.0;
    }

    private static function looksLikeLongitude(float $value): bool
    {
        return $value >= 19.0 && $value <= 180.0;
    }

    private static function positionsEqual(mixed $a, mixed $b): bool
    {
        if (! is_array($a) || ! is_array($b) || count($a) < 2 || count($b) < 2) {
            return false;
        }

        return abs((float) $a[0] - (float) $b[0]) < self::COORD_EPSILON
            && abs((float) $a[1] - (float) $b[1]) < self::COORD_EPSILON;
    }
}
