<?php

namespace App\Console\Commands;

use App\Domain\Product\Entity\ProductIngredient;
use App\Domain\Product\Repository\ProductRepository;
use App\Infrastructure\Product\Model\PRD_Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ProductsExplodeConsistToIngredientsCommand extends Command
{
    protected $signature = 'products:consist-to-ingredients
                            {--dry-run : Ничего не писать в БД}
                            {--limit= : Ограничить количество товаров}
                            {--force : Перезаписать состав даже если уже есть ингредиенты}
                            {--separator= : Переопределить разделитель (regex). По умолчанию: /[,\n;]+/}';

    protected $description = 'Заполнить PRD_product_ingredients из текстового состава (PRD_products.description, исторически из products.consist)';

    public function handle(ProductRepository $repo): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $separator = $this->option('separator') ?: '/[,\n;]+/u';

        $query = PRD_Product::query()
            ->withCount('ingredients')
            ->orderBy('id');

        if (!$force) {
            $query->whereDoesntHave('ingredients');
        }

        $query->whereNotNull('description')->where('description', '!=', '');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $products = $query->get();
        if ($products->isEmpty()) {
            $this->info('Нет товаров для обработки.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Найдено товаров: %d', $products->count()));
        if ($dryRun) {
            $this->warn('dry-run: изменения в БД отключены.');
        }
        if ($force) {
            $this->warn('force: состав будет перезаписан.');
        }

        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($products as $model) {
                $id = (int) $model->id;
                $text = (string) ($model->description ?? '');
                $names = $this->explodeConsist($text, $separator);

                if ($names === []) {
                    $skipped++;
                    continue;
                }

                $entity = $repo->findById($id);
                if (!$entity) {
                    $skipped++;
                    continue;
                }

                $ingredients = array_map(
                    fn (string $name) => ProductIngredient::create($this->toUtf8($name)),
                    $names,
                );

                $entity->setIngredients($ingredients);

                if ($dryRun) {
                    $this->line(sprintf('[dry-run] #%d: %d ингредиентов', $id, count($ingredients)));
                    $updated++;
                    continue;
                }

                try {
                    $repo->save($entity);
                    $updated++;
                } catch (\Throwable $e) {
                    // Не валим всю команду из-за одного кривого состава
                    $skipped++;
                    $this->error(sprintf('Товар #%d: %s', $id, $e->getMessage()));
                    if (!empty($names)) {
                        $this->line('Проблемный ингредиент (hex): ' . bin2hex((string) $names[0]));
                    }
                    continue;
                }
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

        $this->info(sprintf('Готово. Обновлено: %d, пропущено: %d.', $updated, $skipped));
        return self::SUCCESS;
    }

    /**
     * @return string[] уникальные имена ингредиентов
     */
    private function explodeConsist(string $text, string $separatorRegex): array
    {
        $raw = $this->toUtf8(trim($text));
        if ($raw === '') return [];

        $parts = preg_split($separatorRegex, $raw) ?: [];

        $out = [];
        foreach ($parts as $p) {
            $s = $this->toUtf8(trim((string) $p));
            $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
            $s = trim($s, " \t\n\r\0\x0B.-—–•");
            if ($s === '') continue;
            $out[] = $s;
        }

        // уникализируем, сохраняя порядок
        $seen = [];
        $uniq = [];
        foreach ($out as $name) {
            $key = mb_strtolower($name, 'UTF-8');
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $uniq[] = $name;
        }

        return $uniq;
    }

    private function toUtf8(string $value): string
    {
        if ($value === '') return '';

        // mb_scrub убирает битые UTF‑8 последовательности (включая "висячие" байты)
        if (function_exists('mb_scrub')) {
            $value = mb_scrub($value, 'UTF-8');
        }

        // Если строка уже валидный UTF‑8 — оставляем как есть (но чистим хвост)
        if ($this->isValidUtf8($value)) {
            $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            $clean = is_string($clean) ? $clean : $value;
            return $this->trimToValidUtf8($clean);
        }

        // Частый кейс: строка почти UTF‑8, но с обрубленным байтом в конце (например, ... d1)
        $utf8Clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        if (is_string($utf8Clean) && $utf8Clean !== '') {
            // iconv иногда оставляет один "висячий" байт — добиваем вручную
            $fixed = $this->trimToValidUtf8($utf8Clean);
            if ($fixed !== '') {
                return $fixed;
            }
        }

        // Частый кейс для старых данных: Windows-1251 в latin1-полях
        $converted = @iconv('Windows-1251', 'UTF-8//IGNORE', $value);
        if (is_string($converted) && $converted !== '') {
            $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $converted);
            $clean = is_string($clean) ? $clean : $converted;
            return $this->trimToValidUtf8($clean);
        }

        // Фоллбек: попробуем просто выкинуть невалидные байты
        $fallback = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        return is_string($fallback) ? $this->trimToValidUtf8($fallback) : '';
    }

    private function trimToValidUtf8(string $value): string
    {
        $s = $value;
        // режем хвост, пока строка не станет валидной UTF‑8
        while ($s !== '' && !$this->isValidUtf8($s)) {
            $s = substr($s, 0, -1);
        }
        return $s;
    }

    private function isValidUtf8(string $value): bool
    {
        // preg_match('//u') — надежная проверка валидности UTF‑8 последовательности
        return preg_match('//u', $value) === 1;
    }
}

