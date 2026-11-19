<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use Illuminate\Database\ConnectionInterface;

/**
 * Legacy→greenfield перенос заказов отключён: схема ORD пересобрана (lines + снимки).
 */
final class OrderMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'order';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();
        $report->skip('order: схема ORD пересобрана, automatic migrate отключён');

        return $report;
    }
}
