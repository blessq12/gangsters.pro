<?php

namespace App\Legacy\Console\Commands;

use App\Infrastructure\Category\Model\PRD_Category;
use App\Models\ProductCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateLegacyCategoriesCommand extends Command
{
    protected $signature = 'products:migrate-legacy-categories
                            {--dry-run : Не записывать в БД, только показать план}
                            {--limit= : Максимум записей (для теста)}';

    protected $description = 'Мигрировать категории из product_categories в PRD_categories и сохранить маппинг id';

    private const MAPPING_FILE = 'legacy_product_migration.json';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info('Миграция категорий: product_categories → PRD_categories');
        if ($dryRun) {
            $this->warn('Режим dry-run: запись в БД и файл маппинга не выполняются.');
        }

        $query = ProductCategory::query()->orderByRaw('COALESCE(`order`, 999)')->orderBy('id');
        if ($limit !== null) {
            $query->limit($limit);
        }
        $oldCategories = $query->get();

        if ($oldCategories->isEmpty()) {
            $this->info('Нет записей в product_categories.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Найдено категорий: %d', $oldCategories->count()));
        $mapping = $this->readMapping();
        $mapping['categories'] = [];

        DB::beginTransaction();
        try {
            foreach ($oldCategories as $old) {
                $slug = $old->uri ?: Str::slug($old->name);
                if (PRD_Category::where('slug', $slug)->exists()) {
                    $slug = $slug . '-legacy-' . $old->id;
                }

                if ($dryRun) {
                    $this->line(sprintf(
                        '[dry-run] Категория #%d "%s" → slug=%s, is_active=%s',
                        $old->id,
                        $old->name,
                        $slug,
                        $old->visible ? 'true' : 'false'
                    ));
                    continue;
                }

                $new = new PRD_Category();
                $new->name = $old->name ?? 'Без названия';
                $new->slug = $slug;
                $new->sort_order = (int) ($old->order ?? 0);
                $new->is_active = (bool) ($old->visible ?? true);
                $new->save();

                $mapping['categories'][(string) $old->id] = $new->id;
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
                $this->writeMapping($mapping);
                $this->info('Маппинг категорий записан в ' . $this->getMappingPath());
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Ошибка: ' . $e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->info(sprintf('Готово. Перенесено категорий: %d.', $dryRun ? $oldCategories->count() : count($mapping['categories'])));

        return self::SUCCESS;
    }

    private function getMappingPath(): string
    {
        return storage_path('app/' . self::MAPPING_FILE);
    }

    private function readMapping(): array
    {
        $path = $this->getMappingPath();
        if (! is_file($path)) {
            return ['categories' => [], 'products' => []];
        }
        $data = @json_decode(file_get_contents($path), true);

        return is_array($data) ? $data : ['categories' => [], 'products' => []];
    }

    private function writeMapping(array $mapping): void
    {
        file_put_contents(
            $this->getMappingPath(),
            json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
