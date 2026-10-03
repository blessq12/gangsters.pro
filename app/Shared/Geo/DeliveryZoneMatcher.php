<?php

namespace App\Shared\Geo;

use App\Domain\Content\ValueObject\DeliveryZone;

/**
 * Выбор первой зоны, в которую попадает точка (зоны не пересекаются).
 */
final class DeliveryZoneMatcher
{
    /**
     * @param  list<DeliveryZone>  $zones
     */
    public static function match(array $zones, ?float $latitude, ?float $longitude): ?DeliveryZone
    {
        if ($latitude === null || $longitude === null || $zones === []) {
            return null;
        }

        foreach ($zones as $zone) {
            if (PointInGeoJsonZone::contains($zone->geometry(), $latitude, $longitude) === true) {
                return $zone;
            }
        }

        return null;
    }
}
