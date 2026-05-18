<?php

namespace Tests\Unit\Infrastructure\LegacyMigration;

use App\Infrastructure\LegacyMigration\LegacyOrderStatusMapper;
use PHPUnit\Framework\TestCase;

final class LegacyOrderStatusMapperTest extends TestCase
{
    public function test_maps_legacy_statuses_to_domain_values(): void
    {
        $mapper = new LegacyOrderStatusMapper;

        $this->assertSame('delivered', $mapper->map(1));
        $this->assertSame('in_transit', $mapper->map(10));
        $this->assertSame('delivered', $mapper->map(11));
        $this->assertSame('delivered', $mapper->map(99));
    }
}
