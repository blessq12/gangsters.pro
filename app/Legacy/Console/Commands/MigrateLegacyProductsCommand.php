<?php

namespace App\Legacy\Console\Commands;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Domain\Product\VO\Nutrition;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Product\Model\PRD_ProductImage;
use App\Infrastructure\Product\Repository\ProductRepository;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrateLegacyProductsCommand extends Command
{
    use ResolvesLegacyImagePath;

    protected $signature = 'products:migrate-legacy-products
                            {--dry-run : Не записывать в БД и не копировать файлы}
                            {--limit= : Максимум товаров для переноса}
                            {--skip-images : Не переносить изображения}';

    protected $description = 'Мигрировать товары из products в PRD_products (и изображения через вариантную логику), записать маппинг id';

    private const MAPPING_FILE = 'legacy_product_migration.json';
    private const UPLOADS_BASE = 'uploads';
    private const PRODUCTS_DIR = 'products';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $skipImages = (bool) $this->option('skip-images');

        $this->info('Миграция товаров: products → PRD_products (+ цены, изображения)');
        if ($dryRun) {
            $this->warn('Режим dry-run: запись в БД и копирование файлов отключены.');
        }
        if ($skipImages) {
            $this->warn('Изображения не переносятся (--skip-images).');
        }

        $mapping = $this->readMapping();
        if (empty($mapping['categories'])) {
            $this->warn('Маппинг категорий пуст. Сначала выполните: php artisan products:migrate-legacy-categories');
        }

        $query = Product::query()->with('imgs')->orderByRaw('COALESCE(`order`, 999)')->orderBy('id');
        if ($limit !== null) {
            $query->limit($limit);
        }
        $oldProducts = $query->get();

        if ($oldProducts->isEmpty()) {
            $this->info('Нет записей в products.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Найдено товаров: %d', $oldProducts->count()));
        $repo = app(ProductRepository::class);
        $mapping['products'] = $mapping['products'] ?? [];
        $migrated = 0;
        $imagesSkipped = 0;

        $storagePublic = storage_path('app/public');
        $uploadsPath = $storagePublic . '/' . self::UPLOADS_BASE;
        $productsPath = $storagePublic . '/' . self::PRODUCTS_DIR;
        if (! $dryRun && ! $skipImages && ! is_dir($productsPath)) {
            File::ensureDirectoryExists($productsPath);
        }

        DB::beginTransaction();
        try {
            foreach ($oldProducts as $old) {
                if ($dryRun) {
                    $this->line(sprintf('[dry-run] Товар #%d "%s", цена=%s', $old->id, $old->name, $old->price));
                    $migrated++;
                    continue;
                }

                $price = (int) round((float) ($old->price ?? 0));
                $nutrition = new Nutrition(0, 0, 0, 0, 'per_100g');
                $product = ProductEntity::create(
                    name: $old->name ?? 'Без названия',
                    description: (string) ($old->description ?? $old->consist ?? ''),
                    nutrition: $nutrition,
                    images: [],
                    ingredients: [],
                    tags: [],
                    price: $price > 0 ? $price : null,
                    articul: $old->sku ? (string) $old->sku : null,
                );

                if (! $old->visible) {
                    $ref = new \ReflectionClass($product);
                    $statusProp = $ref->getProperty('status');
                    $statusProp->setAccessible(true);
                    $statusProp->setValue($product, ProductEntity::STATUS_ARCHIVED);
                }

                $repo->save($product);
                $newId = $product->id();
                if ($newId === null) {
                    $this->error('Не удалось получить id после сохранения товара ' . $old->id);
                    continue;
                }

                PRD_Product::whereKey($newId)->update([
                    'slug' => Str::slug($old->name) . '-legacy-' . $old->id,
                ]);

                $mapping['products'][(string) $old->id] = $newId;
                $migrated++;

                if (! $skipImages && $old->relationLoaded('imgs') && $old->imgs->isNotEmpty()) {
                    $imgs = $old->imgs->sortBy('id')->values();
                    foreach ($imgs as $index => $oldImage) {
                        $sourcePath = $this->resolveImagePath($oldImage->path, $uploadsPath);
                        if ($sourcePath === null || ! is_file($sourcePath)) {
                            $imagesSkipped++;
                            continue;
                        }
                        $ext = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
                        $basename = 'migrated-' . $newId . '-' . $index . '.' . $ext;
                        $relativeDest = self::PRODUCTS_DIR . '/' . $basename;
                        $destFull = $storagePublic . '/' . $relativeDest;
                        if (! File::copy($sourcePath, $destFull)) {
                            $imagesSkipped++;
                            continue;
                        }
                        $prdImage = new PRD_ProductImage();
                        $prdImage->product_id = $newId;
                        $prdImage->sort_order = $index;
                        $prdImage->thumb_path = $relativeDest;
                        $prdImage->save();
                    }
                }
            }

            DB::commit();
            $this->writeMapping($mapping);
            $this->info('Маппинг товаров записан в ' . $this->getMappingPath());
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Ошибка: ' . $e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->info(sprintf('Готово. Перенесено товаров: %d. Пропущено изображений: %d.', $migrated, $imagesSkipped));

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
