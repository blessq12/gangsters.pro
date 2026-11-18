<?php

namespace App\Console\Commands;

use App\Infrastructure\Storage\Migration\StorageSchemaMigrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Перенос данных из старой физической схемы (PRD_/users/DLV_configuration/…)
 * в greenfield (CAT_/STF_/DLV_configurations/…).
 *
 * Пример:
 * php artisan storage:migrate-schema --source-database=gangsta_old --dry-run
 * php artisan storage:migrate-schema --source-database=gangsta_old --truncate-target
 * php artisan storage:migrate-schema --source-database=gangsta_old --only=catalog,staff
 */
final class MigrateStorageSchemaCommand extends Command
{
    protected $signature = 'storage:migrate-schema
        {--source-connection=mysql : Connection со СТАРОЙ схемой}
        {--source-database= : Имя БД со старыми таблицами (переопределяет database connection)}
        {--target-connection=mysql : Connection с НОВОЙ схемой}
        {--only= : CSV ключей BC: catalog,staff,crm,company,delivery,marketing,order,promotion,production,warehouse,sanctum}
        {--truncate-target : Очистить целевые таблицы BC перед записью}
        {--dry-run : Только посчитать/проверить, без записи}';

    protected $description = 'Мигрирует данные из старых таблиц хранения в greenfield-схему';

    public function handle(StorageSchemaMigrator $migrator): int
    {
        $sourceName = $this->registerSourceConnection();
        $targetName = (string) $this->option('target-connection');
        $dryRun = (bool) $this->option('dry-run');
        $truncate = (bool) $this->option('truncate-target');
        $only = $this->parseOnly();

        $this->info(sprintf(
            'storage:migrate-schema source=%s(%s) target=%s dry-run=%s truncate=%s only=%s',
            $sourceName,
            DB::connection($sourceName)->getDatabaseName(),
            $targetName,
            $dryRun ? 'yes' : 'no',
            $truncate ? 'yes' : 'no',
            $only === null ? 'all' : implode(',', $only),
        ));

        try {
            DB::connection($sourceName)->getPdo();
            DB::connection($targetName)->getPdo();
        } catch (\Throwable $e) {
            $this->error('Нет подключения: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($only !== null) {
            $unknown = array_diff($only, $migrator->availableKeys());
            if ($unknown !== []) {
                $this->error('Неизвестные --only: '.implode(',', $unknown));
                $this->line('Доступно: '.implode(',', $migrator->availableKeys()));

                return self::FAILURE;
            }
        }

        try {
            $report = DB::connection($targetName)->transaction(function () use (
                $migrator,
                $sourceName,
                $targetName,
                $dryRun,
                $truncate,
                $only,
            ) {
                return $migrator->migrate(
                    source: DB::connection($sourceName),
                    target: DB::connection($targetName),
                    dryRun: $dryRun,
                    truncateTarget: $truncate,
                    onlyKeys: $only,
                );
            });
        } catch (\Throwable $e) {
            $this->error('Миграция упала: '.$e->getMessage());

            return self::FAILURE;
        }

        $rows = [];
        foreach ($report->read() as $table => $count) {
            $rows[] = ['read', $table, $count];
        }
        foreach ($report->written() as $table => $count) {
            $rows[] = ['written', $table, $count];
        }
        if ($rows !== []) {
            $this->table(['op', 'table', 'count'], $rows);
        }

        foreach ($report->skipped() as $skip) {
            $this->warn('skip: '.$skip);
        }
        foreach ($report->notes() as $note) {
            $this->line('note: '.$note);
        }

        $this->info($dryRun ? 'Dry-run завершён.' : 'Миграция завершена.');

        return self::SUCCESS;
    }

    private function registerSourceConnection(): string
    {
        $base = (string) $this->option('source-connection');
        $database = $this->option('source-database');

        if ($database === null || $database === '') {
            return $base;
        }

        $name = 'storage_schema_source';
        $config = config('database.connections.'.$base);
        if (! is_array($config)) {
            throw new \RuntimeException("Connection [{$base}] не найден в config/database.php");
        }

        $config['database'] = (string) $database;
        config(["database.connections.{$name}" => $config]);
        DB::purge($name);

        return $name;
    }

    /**
     * @return list<string>|null
     */
    private function parseOnly(): ?array
    {
        $raw = $this->option('only');
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        $parts = array_filter(array_map(
            static fn (string $part): string => strtolower(trim($part)),
            explode(',', (string) $raw),
        ));

        return array_values(array_unique($parts));
    }
}
