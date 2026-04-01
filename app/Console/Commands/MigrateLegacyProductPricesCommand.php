<?php

namespace App\Console\Commands;

use App\Infrastructure\Product\Model\PRD_Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyProductPricesCommand extends Command
{
    protected $signature = 'products:migrate-legacy-prices
                            {--dry-run : Не записывать в БД}
                            {--limit= : Максимум товаров для обработки}
                            {--only-empty : Обновлять только товары без цены}';

    protected $description = 'Смаппить цены из legacy products.price в PRD_products.price (приоритет: артикул, затем маппинг/legacy slug)';

    private const MAPPING_FILE = 'legacy_product_migration.json';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $onlyEmpty = (bool) $this->option('only-empty');

        $this->info('Миграция цен: products.price -> PRD_products.price');
        if ($dryRun) {
            $this->warn('Режим dry-run: запись в БД отключена.');
        }
        if ($onlyEmpty) {
            $this->warn('Режим only-empty: обновляются только PRD-товары без цены.');
        }
        $this->warn('Стратегия сопоставления: SKU/артикул -> mapping file -> legacy slug.');

        $mapping = $this->readMapping();
        $productMap = $mapping['products'] ?? [];
        $hasMapping = $productMap !== [];

        if (!$hasMapping) {
            $this->warn('Маппинг товаров пуст: использую fallback по slug формата *-legacy-{legacyId}.');
        }

        $legacyQuery = DB::table('products')->orderBy('id');
        if ($hasMapping) {
            $legacyIds = array_map('intval', array_keys($productMap));
            sort($legacyIds);
            $legacyQuery->whereIn('id', $legacyIds);
        }
        if ($limit !== null) {
            $legacyQuery->limit($limit);
        }

        $legacyProducts = $legacyQuery->get(['id', 'name', 'price', 'sku']);

        if ($legacyProducts->isEmpty()) {
            $this->info('Нет записей в products для маппинга цен.');
            return self::SUCCESS;
        }

        $updated = 0;
        $skippedNoMap = 0;
        $skippedNotFound = 0;
        $skippedOnlyEmpty = 0;

        DB::beginTransaction();
        try {
            foreach ($legacyProducts as $legacy) {
                /** @var PRD_Product|null $newProduct */
                $newProduct = $this->findByArticul($legacy->sku);
                $newProductId = $productMap[(string) $legacy->id] ?? null;
                if ($newProduct === null) {
                    $newProduct = $newProductId !== null
                        ? PRD_Product::query()->find($newProductId)
                        : $this->findByLegacySlug($legacy->id);
                }

                if ($newProductId === null && $newProduct === null) {
                    $skippedNoMap++;
                    continue;
                }

                if ($newProduct === null) {
                    $skippedNotFound++;
                    continue;
                }

                $priceRub = (int) round((float) ($legacy->price ?? 0));
                if ($onlyEmpty && $newProduct->price !== null) {
                    $skippedOnlyEmpty++;
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf(
                        '[dry-run] legacy #%d "%s" -> prd #%d, price=%s',
                        $legacy->id,
                        $legacy->name ?? 'Без названия',
                        (int) $newProduct->id,
                        $priceRub > 0 ? (string) $priceRub : 'null'
                    ));
                    $updated++;
                    continue;
                }

                $newProduct->price = $priceRub > 0 ? $priceRub : null;
                $newProduct->save();
                $updated++;
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Ошибка: ' . $e->getMessage());
            report($e);
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Готово. Обновлено: %d, пропущено без маппинга: %d, не найдено PRD-товаров: %d, пропущено only-empty: %d.',
            $updated,
            $skippedNoMap,
            $skippedNotFound,
            $skippedOnlyEmpty
        ));

        return self::SUCCESS;
    }

    private function readMapping(): array
    {
        $path = storage_path('app/' . self::MAPPING_FILE);
        if (!is_file($path)) {
            return ['categories' => [], 'products' => []];
        }

        $data = @json_decode(file_get_contents($path), true);

        return is_array($data) ? $data : ['categories' => [], 'products' => []];
    }

    private function findByLegacySlug(int $legacyId): ?PRD_Product
    {
        return PRD_Product::query()
            ->where('slug', 'like', '%-legacy-' . $legacyId)
            ->orderBy('id')
            ->first();
    }

    private function findByArticul(mixed $sku): ?PRD_Product
    {
        $articul = trim((string) $sku);
        if ($articul === '') {
            return null;
        }

        return PRD_Product::query()
            ->where('articul', $articul)
            ->orderBy('id')
            ->first();
    }
}
