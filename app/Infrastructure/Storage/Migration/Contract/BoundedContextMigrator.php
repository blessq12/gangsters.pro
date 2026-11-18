<?php

namespace App\Infrastructure\Storage\Migration\Contract;

use App\Infrastructure\Storage\Migration\MigrationReport;
use Illuminate\Database\ConnectionInterface;

interface BoundedContextMigrator
{
    public function key(): string;

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport;
}
