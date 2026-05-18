<?php

namespace Tests\Feature\Console;

use App\Infrastructure\LegacyMigration\LegacyMigrationEntityType;
use App\Infrastructure\LegacyMigration\LegacyMigrationMapRepository;
use App\Infrastructure\LegacyMigration\LegacyTableDropper;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class DropLegacyTablesCommandTest extends TestCase
{
    public function test_aborts_when_legacy_orders_not_fully_mapped(): void
    {
        $this->mock(LegacyTableDropper::class, function ($mock): void {
            $mock->shouldReceive('countLegacyOrders')->once()->andReturn(100);
            $mock->shouldReceive('foreignKeysReferencingLegacyFromOutside')->never();
            $mock->shouldReceive('dropAll')->never();
        });

        $this->mock(LegacyMigrationMapRepository::class, function ($mock): void {
            $mock->shouldReceive('countByEntityType')
                ->once()
                ->with(LegacyMigrationEntityType::LEGACY_ORDER)
                ->andReturn(50);
        });

        $this->assertSame(1, Artisan::call('legacy:drop-tables'));
    }

    public function test_aborts_when_external_foreign_keys_reference_legacy(): void
    {
        $this->mock(LegacyTableDropper::class, function ($mock): void {
            $mock->shouldReceive('countLegacyOrders')->once()->andReturn(0);
            $mock->shouldReceive('foreignKeysReferencingLegacyFromOutside')->once()->andReturn([
                [
                    'table' => 'ORD_orders',
                    'constraint' => 'ord_orders_legacy_fk',
                    'references' => 'orders',
                ],
            ]);
            $mock->shouldReceive('dropAll')->never();
        });

        $this->mock(LegacyMigrationMapRepository::class, function ($mock): void {
            $mock->shouldReceive('countByEntityType')
                ->once()
                ->with(LegacyMigrationEntityType::LEGACY_ORDER)
                ->andReturn(0);
        });

        $this->assertSame(1, Artisan::call('legacy:drop-tables'));
    }

    public function test_dry_run_lists_tables_without_dropping(): void
    {
        $this->mock(LegacyTableDropper::class, function ($mock): void {
            $mock->shouldReceive('countLegacyOrders')->once()->andReturn(0);
            $mock->shouldReceive('foreignKeysReferencingLegacyFromOutside')->once()->andReturn([]);
            $mock->shouldReceive('dropAll')->once()->with(true)->andReturn(['order_items', 'orders']);
        });

        $this->mock(LegacyMigrationMapRepository::class, function ($mock): void {
            $mock->shouldReceive('countByEntityType')
                ->once()
                ->with(LegacyMigrationEntityType::LEGACY_ORDER)
                ->andReturn(0);
        });

        $this->assertSame(0, Artisan::call('legacy:drop-tables', ['--dry-run' => true]));
        $this->assertStringContainsString('order_items', Artisan::output());
    }
}
