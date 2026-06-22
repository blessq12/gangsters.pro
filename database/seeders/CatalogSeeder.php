<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Infrastructure\Catalog\Model\PRD_Category;
use App\Infrastructure\Catalog\Model\PRD_Product;
use App\Infrastructure\Catalog\Model\PRD_Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CatalogSeeder extends Seeder
{
    private const CATALOG_SOURCE_URL = 'https://gangsta-sushi.ru/api/all-goods';

    public function run(): void
    {
        $categoriesPayload = $this->fetchCategoriesPayload();

        DB::transaction(function () use ($categoriesPayload): void {
            $tags = $this->seedTags();
            $categories = $this->seedCategories($categoriesPayload);
            $products = $this->seedProducts($categoriesPayload);
            $this->attachCategoryItems($categoriesPayload, $categories, $products);
            $this->attachProductTags($categoriesPayload, $tags, $products);
            $this->purgeObsoleteRecords($categoriesPayload);
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchCategoriesPayload(): array
    {
        $response = Http::timeout(30)
            ->retry(2, 500)
            ->get(self::CATALOG_SOURCE_URL);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Не удалось загрузить каталог: HTTP '.$response->status(),
            );
        }

        $payload = $response->json();

        if (! is_array($payload) || ! array_is_list($payload)) {
            throw new RuntimeException('Некорректный формат каталога: ожидался список категорий.');
        }

        return $payload;
    }

    /**
     * @return array<string, PRD_Tag>
     */
    private function seedTags(): array
    {
        $definitions = [
            'hit' => ['label' => 'Хит', 'color' => 'amber', 'sort_order' => 0],
            'spicy' => ['label' => 'Острое', 'color' => 'rose', 'sort_order' => 1],
            'kids_allow' => ['label' => 'Детям', 'color' => 'sky', 'sort_order' => 2],
            'onion' => ['label' => 'Лук', 'color' => 'violet', 'sort_order' => 3],
            'garlic' => ['label' => 'Чеснок', 'color' => 'orange', 'sort_order' => 4],
        ];

        $tags = [];

        foreach ($definitions as $code => $attributes) {
            $tags[$code] = PRD_Tag::query()->updateOrCreate(
                ['code' => $code],
                [
                    'label' => $attributes['label'],
                    'color' => $attributes['color'],
                    'sort_order' => $attributes['sort_order'],
                    'is_active' => true,
                ],
            );
        }

        return $tags;
    }

    /**
     * @param  list<array<string, mixed>>  $categoriesPayload
     * @return array<string, PRD_Category>
     */
    private function seedCategories(array $categoriesPayload): array
    {
        $categories = [];

        foreach ($categoriesPayload as $row) {
            $slug = $this->stringValue($row['uri'] ?? null);

            if ($slug === '') {
                continue;
            }

            $categories[$slug] = PRD_Category::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $this->stringValue($row['name'] ?? $slug),
                    'sort_order' => (int) ($row['order'] ?? 0),
                    'is_active' => (bool) ($row['visible'] ?? true),
                ],
            );
        }

        return $categories;
    }

    /**
     * @param  list<array<string, mixed>>  $categoriesPayload
     * @return array<string, PRD_Product>
     */
    private function seedProducts(array $categoriesPayload): array
    {
        $products = [];

        foreach ($categoriesPayload as $categoryRow) {
            $categorySlug = $this->stringValue($categoryRow['uri'] ?? null);
            $categoryName = $this->stringValue($categoryRow['name'] ?? '');
            $countsAsRoll = $this->categoryCountsAsRoll($categorySlug, $categoryName);

            foreach ($this->productsFromCategoryRow($categoryRow) as $row) {
                $sku = $this->stringValue($row['sku'] ?? null);

                if ($sku === '') {
                    continue;
                }

                $products[$sku] = PRD_Product::query()->updateOrCreate(
                    ['sku' => $sku],
                    [
                        'name' => $this->stringValue($row['name'] ?? $sku),
                        'slug' => $sku,
                        'description' => $this->nullableString($row['consist'] ?? null),
                        'status' => ($row['visible'] ?? true)
                            ? ProductStatus::Active->value
                            : ProductStatus::Archived->value,
                        'catalog_kind' => CatalogItemKind::Product->value,
                        'price' => $this->parsePrice($row['price'] ?? null),
                        'calories' => 0,
                        'proteins' => 0,
                        'fats' => 0,
                        'carbs' => 0,
                        'nutrition_basis' => 'per_100g',
                        'ingredients' => null,
                        'meta_counts_as_roll' => $countsAsRoll,
                        'meta_gift_candidate' => false,
                        'meta_is_complement_set' => false,
                        'archived_at' => ($row['visible'] ?? true) ? null : now(),
                    ],
                );
            }
        }

        return $products;
    }

    /**
     * @param  list<array<string, mixed>>  $categoriesPayload
     * @param  array<string, PRD_Category>  $categories
     * @param  array<string, PRD_Product>  $products
     */
    private function attachCategoryItems(
        array $categoriesPayload,
        array $categories,
        array $products,
    ): void {
        foreach ($categoriesPayload as $categoryRow) {
            $categorySlug = $this->stringValue($categoryRow['uri'] ?? null);
            $category = $categories[$categorySlug] ?? null;

            if ($category === null) {
                continue;
            }

            $sync = [];

            foreach ($this->productsFromCategoryRow($categoryRow) as $row) {
                $sku = $this->stringValue($row['sku'] ?? null);
                $product = $products[$sku] ?? null;

                if ($product === null) {
                    continue;
                }

                $sortOrder = (int) (($row['pivot']['order'] ?? $row['order'] ?? 0));
                $sync[$product->id] = ['sort_order' => $sortOrder];
            }

            $category->products()->sync($sync);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $categoriesPayload
     * @param  array<string, PRD_Tag>  $tags
     * @param  array<string, PRD_Product>  $products
     */
    private function attachProductTags(
        array $categoriesPayload,
        array $tags,
        array $products,
    ): void {
        $assignments = [];

        foreach ($categoriesPayload as $categoryRow) {
            foreach ($this->productsFromCategoryRow($categoryRow) as $row) {
                $sku = $this->stringValue($row['sku'] ?? null);

                if ($sku === '') {
                    continue;
                }

                $tagCodes = $this->resolveTagCodes($row);
                $assignments[$sku] = array_values(array_unique([
                    ...($assignments[$sku] ?? []),
                    ...$tagCodes,
                ]));
            }
        }

        foreach ($assignments as $sku => $tagCodes) {
            $product = $products[$sku] ?? null;

            if ($product === null) {
                continue;
            }

            $tagIds = array_map(
                static fn (string $code): int => $tags[$code]->id,
                $tagCodes,
            );

            $product->tags()->sync($tagIds);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    private function resolveTagCodes(array $row): array
    {
        $map = [
            'hit' => 'hit',
            'spicy' => 'spicy',
            'kidsAllow' => 'kids_allow',
            'onion' => 'onion',
            'garlic' => 'garlic',
        ];

        $codes = [];

        foreach ($map as $apiField => $tagCode) {
            if (! empty($row[$apiField])) {
                $codes[] = $tagCode;
            }
        }

        return $codes;
    }

    /**
     * @param  list<array<string, mixed>>  $categoriesPayload
     */
    private function purgeObsoleteRecords(array $categoriesPayload): void
    {
        $categorySlugs = [];
        $productSkus = [];

        foreach ($categoriesPayload as $categoryRow) {
            $slug = $this->stringValue($categoryRow['uri'] ?? null);

            if ($slug !== '') {
                $categorySlugs[] = $slug;
            }

            foreach ($this->productsFromCategoryRow($categoryRow) as $row) {
                $sku = $this->stringValue($row['sku'] ?? null);

                if ($sku !== '') {
                    $productSkus[] = $sku;
                }
            }
        }

        PRD_Product::query()
            ->where(function ($query) use ($productSkus): void {
                $query
                    ->whereNull('sku')
                    ->orWhereNotIn('sku', array_values(array_unique($productSkus)));
            })
            ->delete();

        PRD_Category::query()
            ->whereNotIn('slug', array_values(array_unique($categorySlugs)))
            ->delete();
    }

    private function categoryCountsAsRoll(string $categorySlug, string $categoryName): bool
    {
        if ($categorySlug === 'nabory') {
            return false;
        }

        return str_contains(mb_strtolower($categoryName), 'ролл');
    }

    /**
     * @param  array<string, mixed>  $categoryRow
     * @return list<array<string, mixed>>
     */
    private function productsFromCategoryRow(array $categoryRow): array
    {
        $products = $categoryRow['products'] ?? [];

        return is_array($products) ? $products : [];
    }

    private function parsePrice(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }

        if (is_float($value)) {
            return max(0, (int) round($value));
        }

        if (! is_string($value) || $value === '') {
            return 0;
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim($value));

        return max(0, (int) round((float) $normalized));
    }

    private function stringValue(mixed $value): string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function nullableString(mixed $value): ?string
    {
        $string = $this->stringValue($value);

        return $string === '' ? null : $string;
    }
}
