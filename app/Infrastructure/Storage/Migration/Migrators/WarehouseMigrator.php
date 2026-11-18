<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

final class WarehouseMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'warehouse';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();

        if (! ConnectionSchema::hasTable($source, 'WH_ingredients')) {
            $report->skip('warehouse: нет WH_ingredients на source');

            return $report;
        }

        if ($source->getName() === $target->getName()
            && $source->getDatabaseName() === $target->getDatabaseName()
        ) {
            $report->skip('warehouse: source=target — WH_* уже целевые таблицы (схема совпадает)');

            return $report;
        }

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, [
                'WH_stock_writeoffs',
                'WH_stock_levels',
                'WH_ingredients',
            ]);
        }

        foreach (['WH_ingredients', 'WH_stock_levels', 'WH_stock_writeoffs'] as $table) {
            if (! ConnectionSchema::hasTable($source, $table)) {
                $report->skip("warehouse: нет {$table}");

                continue;
            }

            $rows = $source->table($table)->orderBy('id')->get();
            $report->addRead($table, $rows->count());
            if ($dryRun) {
                continue;
            }

            foreach ($rows as $row) {
                $payload = (array) $row;
                $id = $payload['id'];
                unset($payload['id']);
                $target->table($table)->updateOrInsert(['id' => $id], $payload);
            }
            $report->addWritten($table, $rows->count());
        }

        return $report;
    }
}
