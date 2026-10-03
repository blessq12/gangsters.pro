<?php

namespace Tests\Unit\Geo;

use App\Domain\Content\ValueObject\DeliveryZone;
use App\Shared\Geo\DeliveryZoneMatcher;
use PHPUnit\Framework\TestCase;

final class DeliveryZoneMatcherTest extends TestCase
{
    public function test_matches_first_zone_containing_point(): void
    {
        $zoneA = $this->zone('a', 'A', 10_000, false, 84.90, 56.45, 0.05);
        $zoneB = $this->zone('b', 'B', 20_000, true, 84.98, 56.51, 0.02);

        $matched = DeliveryZoneMatcher::match(
            [$zoneA, $zoneB],
            56.5129,
            84.9861,
        );

        $this->assertInstanceOf(DeliveryZone::class, $matched);
        $this->assertSame('b', $matched->id());
    }

    public function test_returns_null_when_point_outside_all_zones(): void
    {
        $zone = $this->zone('a', 'A', 10_000, false, 84.90, 56.45, 0.02);

        $matched = DeliveryZoneMatcher::match([$zone], 0.0, 0.0);

        $this->assertNull($matched);
    }

    public function test_returns_null_without_coordinates(): void
    {
        $zone = $this->zone('a', 'A', 10_000, false, 84.90, 56.45, 0.02);

        $this->assertNull(DeliveryZoneMatcher::match([$zone], null, 84.9));
        $this->assertNull(DeliveryZoneMatcher::match([$zone], 56.5, null));
    }

    private function zone(
        string $id,
        string $name,
        int $fee,
        bool $remote,
        float $lng,
        float $lat,
        float $delta,
    ): DeliveryZone {
        return new DeliveryZone(
            id: $id,
            name: $name,
            deliveryFeeKopecks: $fee,
            isRemote: $remote,
            geometry: [
                'type' => 'Polygon',
                'coordinates' => [[
                    [$lng - $delta, $lat - $delta],
                    [$lng + $delta, $lat - $delta],
                    [$lng + $delta, $lat + $delta],
                    [$lng - $delta, $lat + $delta],
                    [$lng - $delta, $lat - $delta],
                ]],
            ],
        );
    }
}
