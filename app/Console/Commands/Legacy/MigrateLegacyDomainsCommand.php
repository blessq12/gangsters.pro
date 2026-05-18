<?php

namespace App\Console\Commands\Legacy;

use App\Infrastructure\LegacyMigration\LegacyClientMigrator;
use App\Infrastructure\LegacyMigration\LegacyMigrationEntityType;
use App\Infrastructure\LegacyMigration\LegacyMigrationMapRepository;
use App\Infrastructure\LegacyMigration\LegacyOrderMigrator;
use Illuminate\Console\Command;

class MigrateLegacyDomainsCommand extends Command
{
    protected $signature = 'legacy:migrate-domains
                            {--dry-run : Только подсчёт без записи}
                            {--only=all : clients|orders|all}';

    protected $description = 'Мигрирует legacy clients/orders в UR_/ORD_ домены';

    public function handle(
        LegacyClientMigrator $clientMigrator,
        LegacyOrderMigrator $orderMigrator,
        LegacyMigrationMapRepository $maps,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $only = (string) $this->option('only');

        if ($dryRun) {
            $this->warn('Режим dry-run: изменения в БД не применяются.');
        }

        if (! $maps->tableExists()) {
            $this->error('Таблица legacy_migration_maps отсутствует. Выполните php artisan migrate.');

            return self::FAILURE;
        }

        if (in_array($only, ['clients', 'all'], true)) {
            $this->info('Миграция клиентов...');
            $clientStats = $clientMigrator->migrate($dryRun);
            $this->table(
                ['created', 'updated', 'addresses', 'skipped'],
                [[$clientStats['created'], $clientStats['updated'], $clientStats['addresses'], $clientStats['skipped']]],
            );
        }

        if (in_array($only, ['orders', 'all'], true)) {
            $this->info('Миграция заказов...');
            $orderStats = $orderMigrator->migrate($dryRun);
            $this->table(
                ['migrated', 'skipped', 'items'],
                [[$orderStats['migrated'], $orderStats['skipped'], $orderStats['items']]],
            );
        }

        if (! $dryRun) {
            $this->line('Карт маппинга:');
            $this->line('  legacy_user: '.$maps->countByEntityType(LegacyMigrationEntityType::LEGACY_USER));
            $this->line('  legacy_order: '.$maps->countByEntityType(LegacyMigrationEntityType::LEGACY_ORDER));
        }

        return self::SUCCESS;
    }
}
