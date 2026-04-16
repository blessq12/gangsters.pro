<?php

namespace App\Legacy\Console\Commands;

use App\Infrastructure\Category\Model\PRD_CategoryProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyRelationsCommand extends Command
{
    protected $signature = 'products:migrate-legacy-relations
                            {--dry-run : Не записывать в БД}
                            {--limit= : Максимум связей для переноса}';

    protected $description = 'Перенести связи категория–товар из category_product в PRD_category_product по маппингу id';

    private const MAPPING_FILE = 'legacy_product_migration.json';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info('Миграция связей: category_product → PRD_category_product');
        if ($dryRun) {
            $this->warn('Режим dry-run: запись в БД отключена.');
        }

        $mapping = $this->readMapping();
        $catMap = $mapping['categories'] ?? [];
        $prodMap = $mapping['products'] ?? [];

        if ($catMap === [] || $prodMap === []) {
            $this->error('Нужен маппинг категорий и товаров. Сначала выполните products:migrate-legacy-categories и products:migrate-legacy-products.');

            return self::FAILURE;
        }

        $query = DB::table('category_product')->orderBy('id');
        if ($limit !== null) {
            $query->limit($limit);
        }
        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->info('Нет записей в category_product.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Найдено связей: %d', $rows->count()));
        $inserted = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $newCategoryId = $catMap[(string) $row->category_id] ?? null;
                $newProductId = $prodMap[(string) $row->product_id] ?? null;

                if ($newCategoryId === null || $newProductId === null) {
                    $skipped++;
                    continue;
                }

                $exists = PRD_CategoryProduct::where('category_id', $newCategoryId)
                    ->where('product_id', $newProductId)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf(
                        '[dry-run] Связь category_id=%d, product_id=%d → new category=%d, product=%d',
                        $row->category_id,
                        $row->product_id,
                        $newCategoryId,
                        $newProductId
                    ));
                    $inserted++;
                    continue;
                }

                PRD_CategoryProduct::create([
                    'category_id' => $newCategoryId,
                    'product_id' => $newProductId,
                    'sort_order' => (int) ($row->order ?? 0),
                ]);
                $inserted++;
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

        $this->info(sprintf('Готово. Создано связей: %d, пропущено: %d.', $inserted, $skipped));

        return self::SUCCESS;
    }

    private function readMapping(): array
    {
        $path = storage_path('app/' . self::MAPPING_FILE);
        if (! is_file($path)) {
            return ['categories' => [], 'products' => []];
        }
        $data = @json_decode(file_get_contents($path), true);

        return is_array($data) ? $data : ['categories' => [], 'products' => []];
    }
}
