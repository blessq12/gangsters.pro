<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use Illuminate\Database\ConnectionInterface;

/**
 * Legacy→greenfield перенос доставки отключён: схема DLV пересобрана.
 */
final class DeliveryMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'delivery';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();
        $report->skip('delivery: схема DLV пересобрана, automatic migrate отключён');

        return $report;
    }
}
