<?php

namespace Tests\Unit\Domain\SystemContent;

use App\Domain\SystemContent\ValueObject\DeliveryZoneGeometry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DeliveryZoneGeometryTest extends TestCase
{
    #[Test]
    public function accepts_valid_polygon_geometry(): void
    {
        $geometry = DeliveryZoneGeometry::fromMixed([
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [84.95, 56.48],
                    [85.05, 56.48],
                    [85.05, 56.52],
                    [84.95, 56.52],
                    [84.95, 56.48],
                ],
            ],
        ]);

        $this->assertNotNull($geometry);
        $this->assertSame('Polygon', $geometry->toArray()['type']);
    }

    #[Test]
    public function accepts_feature_wrapper(): void
    {
        $geometry = DeliveryZoneGeometry::fromMixed([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [84.95, 56.48],
                        [85.05, 56.48],
                        [85.05, 56.52],
                        [84.95, 56.52],
                        [84.95, 56.48],
                    ],
                ],
            ],
            'properties' => [],
        ]);

        $this->assertNotNull($geometry);
    }

    #[Test]
    public function null_and_empty_string_return_null(): void
    {
        $this->assertNull(DeliveryZoneGeometry::fromMixed(null));
        $this->assertNull(DeliveryZoneGeometry::fromMixed(''));
    }

    #[Test]
    public function rejects_ring_with_too_few_points(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('минимум 4 точки');

        DeliveryZoneGeometry::fromMixed([
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [84.95, 56.48],
                    [85.05, 56.48],
                ],
            ],
        ]);
    }

    #[Test]
    public function rejects_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DeliveryZoneGeometry::fromMixed([
            'type' => 'Point',
            'coordinates' => [84.95, 56.48],
        ]);
    }

    #[Test]
    public function normalizes_almost_closed_ring_with_float_drift(): void
    {
        $geometry = DeliveryZoneGeometry::fromMixed([
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [84.9500000001, 56.48],
                    [85.05, 56.48],
                    [85.05, 56.52],
                    [84.95, 56.52],
                    [84.95, 56.48],
                ],
            ],
        ]);

        $this->assertNotNull($geometry);
        $ring = $geometry->toArray()['coordinates'][0];
        $first = $ring[0];
        $last = $ring[count($ring) - 1];
        $this->assertEqualsWithDelta($first[0], $last[0], 0.000001);
        $this->assertEqualsWithDelta($first[1], $last[1], 0.000001);
    }
}
