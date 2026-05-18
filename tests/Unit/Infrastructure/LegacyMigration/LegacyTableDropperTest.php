<?php

namespace Tests\Unit\Infrastructure\LegacyMigration;

use App\Infrastructure\LegacyMigration\LegacyTableDropper;
use PHPUnit\Framework\TestCase;

final class LegacyTableDropperTest extends TestCase
{
    public function test_drop_order_lists_children_before_parents(): void
    {
        $dropper = new LegacyTableDropper;
        $tables = $dropper->legacyTableNames();

        $this->assertSame('order_items', $tables[0]);
        $this->assertTrue(
            array_search('order_items', $tables, true) < array_search('orders', $tables, true),
        );
        $this->assertTrue(
            array_search('orders', $tables, true) < array_search('products', $tables, true),
        );
    }
}
