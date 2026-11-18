<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

final class SanctumTokenMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'sanctum';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();

        if (! ConnectionSchema::hasTable($source, 'personal_access_tokens')) {
            $report->skip('sanctum: нет personal_access_tokens на source');

            return $report;
        }

        $rows = $source->table('personal_access_tokens')->orderBy('id')->get();
        $report->addRead('personal_access_tokens', $rows->count());
        if ($dryRun) {
            $report->note('sanctum: App\\Models\\User → App\\Infrastructure\\Staff\\Model\\STF_Employee');

            return $report;
        }

        if ($truncateTarget
            && ! ($source->getName() === $target->getName()
                && $source->getDatabaseName() === $target->getDatabaseName())
        ) {
            ConnectionSchema::truncate($target, ['personal_access_tokens']);
        }

        $written = 0;
        foreach ($rows as $row) {
            $type = (string) $row->tokenable_type;
            if ($type === 'App\\Models\\User' || $type === 'App\Models\User') {
                $type = 'App\\Infrastructure\\Staff\\Model\\STF_Employee';
            }

            $target->table('personal_access_tokens')->updateOrInsert(
                ['id' => $row->id],
                [
                    'tokenable_type' => $type,
                    'tokenable_id' => $row->tokenable_id,
                    'name' => $row->name,
                    'token' => $row->token,
                    'abilities' => $row->abilities,
                    'last_used_at' => $row->last_used_at ?? null,
                    'expires_at' => $row->expires_at ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
            $written++;
        }
        $report->addWritten('personal_access_tokens', $written);

        return $report;
    }
}
