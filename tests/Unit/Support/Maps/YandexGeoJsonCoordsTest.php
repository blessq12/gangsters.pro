<?php

namespace Tests\Unit\Support\Maps;

use App\Support\Maps\YandexGeoJsonCoords;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class YandexGeoJsonCoordsTest extends TestCase
{
    #[Test]
    public function converts_geojson_tomsk_position_to_ymaps(): void
    {
        $ymaps = YandexGeoJsonCoords::geoJsonPositionToYmaps(84.95, 56.48);

        $this->assertSame([56.48, 84.95], $ymaps);
    }

    #[Test]
    public function converts_ymaps_tomsk_position_to_geojson(): void
    {
        $geo = YandexGeoJsonCoords::ymapsPositionToGeoJson(56.48, 84.95);

        $this->assertSame([84.95, 56.48], $geo);
    }

    #[Test]
    public function round_trips_tomsk_corner(): void
    {
        $lon = 85.05;
        $lat = 56.52;

        $ymaps = YandexGeoJsonCoords::geoJsonPositionToYmaps($lon, $lat);
        $this->assertNotNull($ymaps);

        $back = YandexGeoJsonCoords::ymapsPositionToGeoJson($ymaps[0], $ymaps[1]);
        $this->assertSame([$lon, $lat], $back);
    }
}
