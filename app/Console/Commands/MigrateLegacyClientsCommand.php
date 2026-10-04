<?php

namespace App\Console\Commands;

use App\Infrastructure\Crm\Migration\LegacyClientMigrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Command: перенос clients + Sanctum-токенов из legacy-БД (main/users) в CRM_clients.
 *
 * Пример:
 * php artisan crm:migrate-legacy-clients \
 *   --host=127.0.0.1 --database=old_db --username=root --password=secret \
 *   --dry-run
 */
final class MigrateLegacyClientsCommand extends Command
{
    protected $signature = 'crm:migrate-legacy-clients
        {--host= : Host legacy-БД}
        {--port= : Port legacy-БД}
        {--database= : Имя legacy-БД}
        {--username= : Username legacy-БД}
        {--password= : Password legacy-БД}
        {--driver= : Driver legacy-БД (mysql/pgsql)}
        {--connection=legacy : Имя connection из config/database.php}
        {--chunk=200 : Размер чанка}
        {--dry-run : Только посчитать/проверить, без записи}
        {--no-preserve-ids : Не сохранять users.id как CRM_clients.id}';

    protected $description = 'Мигрирует users + Sanctum-токены из legacy-БД в CRM_clients';

    public function handle(LegacyClientMigrator $migrator): int
    {
        $connection = (string) $this->option('connection');
        $this->applyRuntimeCredentials($connection);

        if (! $this->assertConnectionConfigured($connection)) {
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $preserveIds = ! (bool) $this->option('no-preserve-ids');
        $chunk = max(1, (int) $this->option('chunk'));

        $this->info(sprintf(
            'Старт миграции: connection=%s dry-run=%s preserve-ids=%s chunk=%d',
            $connection,
            $dryRun ? 'yes' : 'no',
            $preserveIds ? 'yes' : 'no',
            $chunk,
        ));

        try {
            DB::connection($connection)->getPdo();
        } catch (\Throwable $e) {
            $this->error('Не удалось подключиться к legacy-БД: '.$e->getMessage());

            return self::FAILURE;
        }

        try {
            $result = $migrator->migrate(
                legacyConnection: $connection,
                dryRun: $dryRun,
                preserveIds: $preserveIds,
                chunk: $chunk,
            );
        } catch (\Throwable $e) {
            $this->error('Миграция упала: '.$e->getMessage());

            return self::FAILURE;
        }

        $report = $result['report'];

        $this->table(
            ['Метрика', 'Значение'],
            [
                ['clients read', $report->clientsRead()],
                ['clients inserted', $report->clientsInserted()],
                ['clients skipped', $report->clientsSkipped()],
                ['tokens read', $report->tokensRead()],
                ['tokens inserted', $report->tokensInserted()],
                ['tokens skipped', $report->tokensSkipped()],
                ['id map size', count($result['idMap'])],
            ],
        );

        foreach ($report->warnings() as $warning) {
            $this->warn($warning);
        }

        if ($dryRun) {
            $this->comment('Dry-run: в целевую БД ничего не писали.');
        } else {
            $this->info('Готово.');
        }

        return self::SUCCESS;
    }

    private function applyRuntimeCredentials(string $connection): void
    {
        $map = [
            'host' => 'host',
            'port' => 'port',
            'database' => 'database',
            'username' => 'username',
            'password' => 'password',
            'driver' => 'driver',
        ];

        $overrides = [];
        foreach ($map as $option => $configKey) {
            $value = $this->option($option);
            if ($value !== null && $value !== '') {
                $overrides[$configKey] = $value;
            }
        }

        if ($overrides === []) {
            return;
        }

        foreach ($overrides as $key => $value) {
            config(["database.connections.{$connection}.{$key}" => $value]);
        }

        DB::purge($connection);
    }

    private function assertConnectionConfigured(string $connection): bool
    {
        $config = config("database.connections.{$connection}");
        if (! is_array($config)) {
            $this->error("Connection [{$connection}] не найден в config/database.php.");

            return false;
        }

        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');

        if ($database === '' || $username === '') {
            $this->error(
                "Для [{$connection}] не заданы database/username. "
                .'Передай --database/--username/--password или LEGACY_DB_* в .env.'
            );

            return false;
        }

        return true;
    }
}
