<?php

namespace App\Console\Commands\Legacy;

use App\Infrastructure\LegacyMigration\LegacyMigrationEntityType;
use App\Infrastructure\LegacyMigration\LegacyMigrationMapRepository;
use App\Infrastructure\LegacyMigration\LegacyTableDropper;
use Illuminate\Console\Command;

class DropLegacyTablesCommand extends Command
{
    protected $signature = 'legacy:drop-tables
                            {--force : Продолжить даже если не все заказы в маппинге}
                            {--dry-run : Показать что будет удалено}';

    protected $description = 'Удаляет legacy-таблицы после миграции в домены';

    public function handle(
        LegacyTableDropper $dropper,
        LegacyMigrationMapRepository $maps,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $legacyOrders = $dropper->countLegacyOrders();
        $mappedOrders = $maps->countByEntityType(LegacyMigrationEntityType::LEGACY_ORDER);

        if ($legacyOrders > 0 && $mappedOrders < $legacyOrders && ! $force) {
            $this->error("Мигрировано {$mappedOrders} из {$legacyOrders} legacy orders. Запустите legacy:migrate-domains или --force.");

            return self::FAILURE;
        }

        if ($legacyOrders > $mappedOrders && $force) {
            $this->warn("Продолжаем с --force: маппинг {$mappedOrders}/{$legacyOrders}.");
        }

        $violations = $dropper->foreignKeysReferencingLegacyFromOutside();
        if ($violations !== []) {
            $this->error('Внешние FK на legacy-таблицы:');
            foreach ($violations as $violation) {
                $this->line("  {$violation['table']}.{$violation['constraint']} → {$violation['references']}");
            }

            return self::FAILURE;
        }

        $dropped = $dropper->dropAll($dryRun);

        if ($dropped === []) {
            $this->info('Legacy-таблицы уже отсутствуют.');

            return self::SUCCESS;
        }

        $prefix = $dryRun ? '[dry-run] Будут удалены: ' : 'Удалены: ';
        $this->info($prefix.implode(', ', $dropped));

        return self::SUCCESS;
    }
}
