<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use Illuminate\Database\ConnectionInterface;

final class StaffMigrator implements BoundedContextMigrator
{
    public function key(): string
    {
        return 'staff';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();

        if (! ConnectionSchema::hasTable($source, 'users')) {
            $report->skip('staff: нет users на source');

            return $report;
        }

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, ['STF_employees']);
        }

        $rows = $source->table('users')->orderBy('id')->get();
        $report->addRead('users', $rows->count());
        if ($dryRun) {
            return $report;
        }

        foreach ($rows as $row) {
            $target->table('STF_employees')->updateOrInsert(
                ['id' => $row->id],
                [
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->tel ?? $row->phone ?? null,
                    'role' => $row->role ?? 'administrator',
                    'password_hash' => $row->password,
                    'is_active' => (bool) ($row->is_active ?? true),
                    'remember_token' => $row->remember_token ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ],
            );
        }
        $report->addWritten('STF_employees', $rows->count());

        return $report;
    }
}
