<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

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
        $sourceTable = ConnectionSchema::hasTable($source, 'PRD_kitchen_tickets')
            ? 'PRD_kitchen_tickets'
            : (ConnectionSchema::hasTable($source, 'KIT_tickets') ? 'KIT_tickets' : null);

        if ($sourceTable === null) {
            $report->skip('production: нет PRD_kitchen_tickets');

            return $report;
        }

        if ($sourceTable === 'KIT_tickets'
            && $source->getName() === $target->getName()
            && $source->getDatabaseName() === $target->getDatabaseName()
        ) {
            $report->skip('production: source=target, KIT_tickets уже целевая таблица');

            return $report;
        }

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, ['KIT_tickets']);
        }

        $rows = $source->table($sourceTable)->orderBy('id')->get();
        $report->addRead($sourceTable, $rows->count());
        if ($dryRun) {
            return $report;
        }

        foreach ($rows as $row) {
            $target->table('KIT_tickets')->updateOrInsert(
                ['id' => $row->id],
                [
                    'order_id' => $row->order_id,
                    'status' => $row->status,
                    'started_at' => $row->started_at ?? null,
                    'completed_at' => $row->completed_at ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
        }
        $report->addWritten('KIT_tickets', $rows->count());

        return $report;
    }
}
