<?php

namespace App\Console\Commands;

use App\Infrastructure\DataClone\CatalogDataCloner;
use App\Infrastructure\DataClone\ClientDataCloner;
use App\Infrastructure\DataClone\CloneSourceFactory;
use App\Infrastructure\DataClone\MarketingDataCloner;
use App\Infrastructure\DataClone\OrderDataCloner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Клонирование legacy-данных (баннеры/акции, каталог, клиенты, заказы) из другой MySQL БД.
 */
final class CloneDataFromDatabaseCommand extends Command
{
    protected $signature = 'data:clone-from-db
        {database : Имя исходной БД}
        {username : Логин MySQL}
        {password? : Пароль MySQL (можно пустой)}
        {--host=127.0.0.1 : Хост исходной БД}
        {--port=3306 : Порт исходной БД}
        {--branch-id=1 : ID филиала для меню и заказов}
        {--only= : Секции через запятую: marketing,catalog,clients,orders}';

    protected $description = 'Клонирует баннеры/акции, каталог, клиентов и заказы из другой MySQL БД';

    /** @var list<string> */
    private const SECTIONS = ['marketing', 'catalog', 'clients', 'orders'];

    public function handle(
        CloneSourceFactory $sourceFactory,
        MarketingDataCloner $marketingCloner,
        CatalogDataCloner $catalogCloner,
        ClientDataCloner $clientCloner,
        OrderDataCloner $orderCloner,
    ): int {
        $database = (string) $this->argument('database');
        $username = (string) $this->argument('username');
        $password = (string) ($this->argument('password') ?? '');
        $host = (string) $this->option('host');
        $port = (int) $this->option('port');
        $branchId = (int) $this->option('branch-id');
        try {
            $sections = $this->resolveSections((string) ($this->option('only') ?? ''));
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($branchId <= 0) {
            $this->error('Нужен валидный --branch-id.');

            return self::FAILURE;
        }

        if (! DB::table('CMP_branches')->where('id', $branchId)->exists()) {
            $this->error("Филиал #{$branchId} не найден в текущей БД.");

            return self::FAILURE;
        }

        try {
            $source = $sourceFactory->make($database, $username, $password, $host, $port);
            $source->getPdo();
        } catch (Throwable $e) {
            $this->error('Не удалось подключиться к исходной БД: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Источник: {$username}@{$host}:{$port}/{$database}");
        $this->info('Секции: '.implode(', ', $sections));
        $this->info("Филиал: #{$branchId}");

        try {
            if (in_array('marketing', $sections, true)) {
                $this->line('→ marketing…');
                $stats = $marketingCloner->clone($source);
                $this->info("  баннеры={$stats['banners']}, акции={$stats['promotions']}");
            }

            if (in_array('catalog', $sections, true)) {
                $this->line('→ catalog…');
                $stats = $catalogCloner->clone($source, $branchId);
                $this->info(sprintf(
                    '  товары=%d, картинки=%d, меню=%d, категории=%d, связи=%d, сеты=%d, линии_сетов=%d, теги=%d, теги_связей=%d',
                    $stats['products'],
                    $stats['images'],
                    $stats['menu_items'],
                    $stats['categories'],
                    $stats['category_items'],
                    $stats['sets'],
                    $stats['set_lines'],
                    $stats['tags'],
                    $stats['entry_tags'],
                ));
            }

            if (in_array('clients', $sections, true)) {
                $this->line('→ clients…');
                $stats = $clientCloner->clone($source);
                $this->info(sprintf(
                    '  клиенты=%d, адреса=%d, избранное=%d (пропущено избранного=%d)',
                    $stats['clients'],
                    $stats['addresses'],
                    $stats['favorites'],
                    $stats['skipped_favorites'],
                ));
            }

            if (in_array('orders', $sections, true)) {
                $this->line('→ orders…');
                $stats = $orderCloner->clone($source, $branchId);
                $this->info(sprintf(
                    '  заказы=%d, линии=%d (пропущено линий=%d)',
                    $stats['orders'],
                    $stats['lines'],
                    $stats['skipped_lines'],
                ));
            }
        } catch (Throwable $e) {
            $this->error('Клонирование упало: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Готово.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function resolveSections(string $only): array
    {
        if (trim($only) === '') {
            return self::SECTIONS;
        }

        $requested = array_values(array_filter(array_map(
            static fn (string $part): string => strtolower(trim($part)),
            explode(',', $only),
        )));

        $unknown = array_diff($requested, self::SECTIONS);
        if ($unknown !== []) {
            throw new \InvalidArgumentException(
                'Неизвестные секции: '.implode(', ', $unknown).'. Допустимо: '.implode(', ', self::SECTIONS)
            );
        }

        $ordered = [];
        foreach (self::SECTIONS as $section) {
            if (in_array($section, $requested, true)) {
                $ordered[] = $section;
            }
        }

        return $ordered;
    }
}
