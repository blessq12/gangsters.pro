<?php

namespace App\Console\Commands;

use App\Infrastructure\Product\Model\PRD_ProductImage;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigrateLegacyProductImagesCommand extends Command
{
    use ResolvesLegacyImagePath;

    protected $signature = 'products:migrate-legacy-images
                            {--dry-run : Не записывать в БД и не копировать файлы}
                            {--replace : Удалить текущие изображения PRD-товара перед переносом}
                            {--limit= : Максимум товаров (по маппингу) для обработки}
                            {--product= : Только один товар: legacy id или PRD id}';

    protected $description = 'Перенести изображения товаров из legacy product_images в PRD_product_images по маппингу';

    private const MAPPING_FILE = 'legacy_product_migration.json';
    private const PRODUCTS_DIR = 'products';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $replace = (bool) $this->option('replace');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $productFilter = $this->option('product');

        $this->info('Миграция изображений: product_images → PRD_product_images');
        if ($dryRun) {
            $this->warn('Режим dry-run: запись в БД и копирование файлов отключены.');
        }

        $mapping = $this->readMapping();
        $productMap = $mapping['products'] ?? [];
        if (empty($productMap)) {
            $this->error('Маппинг товаров пуст. Сначала выполните: php artisan products:migrate-legacy-products');
            return self::FAILURE;
        }

        if ($productFilter !== null) {
            $productFilter = trim($productFilter);
            if ($productFilter === '') {
                $this->error('Опция --product не должна быть пустой.');
                return self::FAILURE;
            }
            if (ctype_digit($productFilter)) {
                $id = (int) $productFilter;
                if (isset($productMap[(string) $id])) {
                    $productMap = [$id => $productMap[(string) $id]];
                } else {
                    $byNewId = array_flip($productMap);
                    $legacyKey = $byNewId[$id] ?? null;
                    if ($legacyKey !== null) {
                        $productMap = [(int) $legacyKey => $id];
                    } else {
                        $this->error('Товар с id ' . $productFilter . ' не найден в маппинге.');
                        return self::FAILURE;
                    }
                }
            }
        }

        if ($limit !== null) {
            $productMap = array_slice($productMap, 0, $limit, true);
        }

        $storagePublic = storage_path('app/public');
        $uploadsPath = $storagePublic . '/' . self::UPLOADS_BASE;
        $productsPath = $storagePublic . '/' . self::PRODUCTS_DIR;
        if (!$dryRun && !is_dir($productsPath)) {
            File::ensureDirectoryExists($productsPath);
        }

        $migratedProducts = 0;
        $migratedImages = 0;
        $skippedImages = 0;

        DB::beginTransaction();
        try {
            foreach ($productMap as $legacyId => $newId) {
                $old = Product::with('imgs')->find($legacyId);
                if ($old === null) {
                    continue;
                }
                $imgs = $old->imgs->sortBy('id')->values();
                if ($imgs->isEmpty()) {
                    continue;
                }

                if (!$dryRun && $replace) {
                    PRD_ProductImage::where('product_id', $newId)->delete();
                }

                $added = 0;
                foreach ($imgs as $index => $oldImage) {
                    $sourcePath = $this->resolveImagePath($oldImage->path, $uploadsPath);
                    if ($sourcePath === null || !is_file($sourcePath)) {
                        $skippedImages++;
                        continue;
                    }
                    if ($dryRun) {
                        $this->line(sprintf('[dry-run] Товар %d → %d: изображение %d — %s', $legacyId, $newId, $index, $oldImage->path));
                        $added++;
                        continue;
                    }
                    $ext = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
                    $basename = 'migrated-' . $newId . '-' . $index . '.' . $ext;
                    $relativeDest = self::PRODUCTS_DIR . '/' . $basename;
                    $destFull = $storagePublic . '/' . $relativeDest;
                    if (!File::copy($sourcePath, $destFull)) {
                        $skippedImages++;
                        continue;
                    }
                    $prdImage = new PRD_ProductImage();
                    $prdImage->product_id = $newId;
                    $prdImage->sort_order = $index;
                    $prdImage->thumb_path = $relativeDest;
                    $prdImage->save();
                    $added++;
                    $migratedImages++;
                }
                if ($added > 0) {
                    $migratedProducts++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Ошибка: ' . $e->getMessage());
            report($e);
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Готово. Товаров с перенесёнными фото: %d. Изображений перенесено: %d. Пропущено: %d.',
            $migratedProducts,
            $migratedImages,
            $skippedImages
        ));
        return self::SUCCESS;
    }

    private function getMappingPath(): string
    {
        return storage_path('app/' . self::MAPPING_FILE);
    }

    private function readMapping(): array
    {
        $path = $this->getMappingPath();
        if (!is_file($path)) {
            return ['categories' => [], 'products' => []];
        }
        $data = @json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : ['categories' => [], 'products' => []];
    }
}
