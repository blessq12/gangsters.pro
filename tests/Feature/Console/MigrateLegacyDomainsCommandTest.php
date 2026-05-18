<?php

namespace Tests\Feature\Console;

use App\Infrastructure\LegacyMigration\LegacyClientMigrator;
use App\Infrastructure\LegacyMigration\LegacyMigrationMapRepository;
use App\Infrastructure\LegacyMigration\LegacyOrderMigrator;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class MigrateLegacyDomainsCommandTest extends TestCase
{
    public function test_dry_run_clients_does_not_invoke_persisting_migrator_methods(): void
    {
        $this->mock(LegacyMigrationMapRepository::class, function ($mock): void {
            $mock->shouldReceive('tableExists')->once()->andReturn(true);
        });

        $this->mock(LegacyClientMigrator::class, function ($mock): void {
            $mock->shouldReceive('migrate')->once()->with(true)->andReturn([
                'created' => 1,
                'updated' => 0,
                'addresses' => 0,
                'skipped' => 0,
            ]);
        });

        $this->mock(LegacyOrderMigrator::class, function ($mock): void {
            $mock->shouldNotReceive('migrate');
        });

        $this->assertSame(0, Artisan::call('legacy:migrate-domains', [
            '--dry-run' => true,
            '--only' => 'clients',
        ]));
    }

    public function test_dry_run_orders_invokes_order_migrator_only(): void
    {
        $this->mock(LegacyMigrationMapRepository::class, function ($mock): void {
            $mock->shouldReceive('tableExists')->once()->andReturn(true);
        });

        $this->mock(LegacyClientMigrator::class, function ($mock): void {
            $mock->shouldNotReceive('migrate');
        });

        $this->mock(LegacyOrderMigrator::class, function ($mock): void {
            $mock->shouldReceive('migrate')->once()->with(true)->andReturn([
                'migrated' => 0,
                'skipped' => 10,
                'items' => 0,
            ]);
        });

        $this->assertSame(0, Artisan::call('legacy:migrate-domains', [
            '--dry-run' => true,
            '--only' => 'orders',
        ]));
    }
}
