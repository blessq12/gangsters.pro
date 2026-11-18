<?php

namespace App\Infrastructure\Storage\Migration;

use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\Migrators\CatalogMigrator;
use App\Infrastructure\Storage\Migration\Migrators\CompanyMigrator;
use App\Infrastructure\Storage\Migration\Migrators\CrmMigrator;
use App\Infrastructure\Storage\Migration\Migrators\DeliveryMigrator;
use App\Infrastructure\Storage\Migration\Migrators\MarketingMigrator;
use App\Infrastructure\Storage\Migration\Migrators\OrderMigrator;
use App\Infrastructure\Storage\Migration\Migrators\ProductionMigrator;
use App\Infrastructure\Storage\Migration\Migrators\PromotionMigrator;
use App\Infrastructure\Storage\Migration\Migrators\SanctumTokenMigrator;
use App\Infrastructure\Storage\Migration\Migrators\StaffMigrator;
use App\Infrastructure\Storage\Migration\Migrators\WarehouseMigrator;
use Illuminate\Database\ConnectionInterface;

/**
 * Orchestrator: Strategy-набор BC-миграторов old schema → greenfield storage.
 */
final class StorageSchemaMigrator
{
    /** @var list<BoundedContextMigrator> */
    private array $migrators;

    public function __construct()
    {
        $this->migrators = [
            new StaffMigrator(),
            new CatalogMigrator(),
            new CompanyMigrator(),
            new CrmMigrator(),
            new DeliveryMigrator(),
            new MarketingMigrator(),
            new OrderMigrator(),
            new PromotionMigrator(),
            new ProductionMigrator(),
            new WarehouseMigrator(),
            new SanctumTokenMigrator(),
        ];
    }

    /**
     * @param  list<string>|null  $onlyKeys
     */
    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
        ?array $onlyKeys = null,
    ): MigrationReport {
        $report = new MigrationReport();
        $only = $onlyKeys === null
            ? null
            : array_fill_keys(array_map('strtolower', $onlyKeys), true);

        foreach ($this->migrators as $migrator) {
            $key = $migrator->key();
            if ($only !== null && ! isset($only[$key])) {
                continue;
            }

            $report->merge($migrator->migrate($source, $target, $dryRun, $truncateTarget));
        }

        return $report;
    }

    /**
     * @return list<string>
     */
    public function availableKeys(): array
    {
        return array_map(
            static fn (BoundedContextMigrator $migrator): string => $migrator->key(),
            $this->migrators,
        );
    }
}
