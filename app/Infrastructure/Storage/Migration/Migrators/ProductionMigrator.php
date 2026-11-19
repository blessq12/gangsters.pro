<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use Illuminate\Database\ConnectionInterface;

/**
 * Legacy→greenfield перенос тикетов отключён: схема KIT пересобрана (ticket lines).
 */
final class ProductionMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'production';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();
        $report->skip('production: схема KIT пересобрана, automatic migrate отключён');

        return $report;
    }
}
