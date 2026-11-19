<?php

namespace Database\Seeders;

use App\Domain\Core\Order\Enum\ProductPromotionRoleKind;
use App\Domain\Supporting\Catalog\Enum\CatalogItemKind;
use App\Domain\Supporting\Catalog\Enum\PriceType;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Infrastructure\Catalog\Model\CAT_Category;
use App\Infrastructure\Catalog\Model\CAT_Item;
use App\Infrastructure\Catalog\Model\CAT_ItemImage;
use App\Infrastructure\Catalog\Model\CAT_Price;
use App\Infrastructure\Catalog\Model\CAT_SetLine;
use App\Infrastructure\Catalog\Model\CAT_Tag;
use App\Infrastructure\Promotion\Model\PRM_ProductRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Каталог из публичного API витрины → greenfield CAT_/PRM_product_roles.
 */
class CatalogSeeder extends Seeder
{
    private const CATALOG_SOURCE_URL = 'https://gangsta-sushi.ru/api/catalog';

    private const CATALOG_SOURCE_ORIGIN = 'https://gangsta-sushi.ru';

    private const KOPECKS_IN_RUBLE = 100;

    public function run(): void
    {
        $payload = $this->fetchCatalogPayload();
        $categoryBlocks = $this->categoryBlocksFromPayload($payload);
        $itemsById = $this->collectItemsById($payload, $categoryBlocks);

        DB::transaction(function () use ($categoryBlocks, $itemsById): void {
            $tags = $this->seedTags($itemsById);
            $categories = $this->seedCategories($categoryBlocks);
            $products = $this->seedItems($itemsById);
            $this->attachCategoryItems($categoryBlocks, $categories, $products);
            $this->attachItemTags($itemsById, $tags, $products);
            $this->seedSetLines($itemsById, $products);
            $this->seedPromotionRoles($itemsById, $products);
            $this->purgeObsoleteRecords($categories, $products);
        });

        // Медиа не качаем в сидере — иначе залипаем на сотнях HTTP.
        // Пути/фокус можно дотянуть отдельно при необходимости.
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCatalogPayload(): array
    {
        $response = Http::timeout(60)
            ->acceptJson()
            ->retry(2, 500)
            ->get(self::CATALOG_SOURCE_URL);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Не удалось загрузить каталог: HTTP '.$response->status(),
            );
        }

        $payload = $response->json();

        if (! is_array($payload) || ! isset($payload['categories']) || ! is_array($payload['categories'])) {
            throw new RuntimeException('Некорректный формат каталога: ожидался объект с categories.');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>
     */
    private function categoryBlocksFromPayload(array $payload): array
    {
        $blocks = [];

        foreach (['categories', 'accompanying_categories'] as $bucket) {
            $rows = $payload[$bucket] ?? [];
            if (! is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                if (! is_array($row) || ! isset($row['category']) || ! is_array($row['category'])) {
                    continue;
                }

                $items = $row['items'] ?? [];
                $blocks[] = [
                    'category' => $row['category'],
                    'items' => is_array($items) ? array_values(array_filter($items, 'is_array')) : [],
                ];
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>  $categoryBlocks
     * @return array<int, array<string, mixed>>
     */
    private function collectItemsById(array $payload, array $categoryBlocks): array
    {
        $itemsById = [];

        foreach ($categoryBlocks as $block) {
            foreach ($block['items'] as $item) {
                $this->indexItem($itemsById, $item);
            }
        }

        $complements = $payload['complement_products'] ?? [];
        if (is_array($complements)) {
            foreach ($complements as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $this->indexItem($itemsById, $item);
                $paidTwin = $item['paid_twin'] ?? null;
                if (is_array($paidTwin)) {
                    $this->indexItem($itemsById, $paidTwin);
                }
            }
        }

        return $itemsById;
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsById
     * @param  array<string, mixed>  $item
     */
    private function indexItem(array &$itemsById, array $item): void
    {
        $id = (int) ($item['id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        $itemsById[$id] = $item;
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsById
     * @return array<string, CAT_Tag>
     */
    private function seedTags(array $itemsById): array
    {
        $definitions = [];

        foreach ($itemsById as $item) {
            $tags = $item['tags'] ?? [];
            if (! is_array($tags)) {
                continue;
            }

            foreach ($tags as $tag) {
                if (! is_array($tag)) {
                    continue;
                }

                $code = $this->stringValue($tag['code'] ?? null);
                if ($code === '') {
                    continue;
                }

                $definitions[$code] = [
                    'label' => $this->stringValue($tag['label'] ?? $code),
                    'color' => $this->stringValue($tag['color'] ?? 'amber') ?: 'amber',
                ];
            }
        }

        $tags = [];
        $sortOrder = 0;

        foreach ($definitions as $code => $attributes) {
            $tags[$code] = CAT_Tag::query()->updateOrCreate(
                ['code' => $code],
                [
                    'label' => $attributes['label'],
                    'color' => $attributes['color'],
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
            $sortOrder++;
        }

        return $tags;
    }

    /**
     * @param  list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>  $categoryBlocks
     * @return array<int, CAT_Category>
     */
    private function seedCategories(array $categoryBlocks): array
    {
        $categories = [];

        foreach ($categoryBlocks as $block) {
            $row = $block['category'];
            $id = (int) ($row['id'] ?? 0);
            $slug = $this->stringValue($row['slug'] ?? null);

            if ($id <= 0 || $slug === '') {
                continue;
            }

            $categories[$id] = CAT_Category::query()->updateOrCreate(
                ['id' => $id],
                [
                    'name' => $this->stringValue($row['name'] ?? $slug),
                    'slug' => $slug,
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'is_accompanying' => (bool) ($row['is_accompanying'] ?? false),
                ],
            );
        }

        return $categories;
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsById
     * @return array<int, CAT_Item>
     */
    private function seedItems(array $itemsById): array
    {
        $products = [];

        foreach ($itemsById as $id => $row) {
            $kind = $this->stringValue($row['kind'] ?? CatalogItemKind::Product->value);
            if ($kind !== CatalogItemKind::Product->value && $kind !== CatalogItemKind::Set->value) {
                $kind = CatalogItemKind::Product->value;
            }

            $statusRaw = $this->stringValue($row['status'] ?? ProductStatus::Active->value);
            $status = $statusRaw === ProductStatus::Archived->value
                ? ProductStatus::Archived->value
                : ProductStatus::Active->value;

            $slug = $this->stringValue($row['slug'] ?? null);
            if ($slug === '') {
                $slug = 'item-'.$id;
            }

            $item = CAT_Item::query()->updateOrCreate(
                ['id' => $id],
                [
                    'name' => $this->stringValue($row['name'] ?? $slug),
                    'slug' => $slug,
                    'sku' => (string) $id,
                    'description' => $this->nullableString($row['description'] ?? null),
                    'status' => $status,
                    'kind' => $kind,
                    'is_system' => false,
                    'weight' => isset($row['weight']) && is_numeric($row['weight'])
                        ? (int) $row['weight']
                        : null,
                    'nutrition' => is_array($row['nutrition'] ?? null) ? $row['nutrition'] : null,
                    'ingredients' => is_array($row['ingredients'] ?? null) ? $row['ingredients'] : null,
                    'archived_at' => $status === ProductStatus::Archived->value ? now() : null,
                ],
            );

            $this->seedPrices($item, $row);
            $products[$id] = $item;
        }

        return $products;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function seedPrices(CAT_Item $item, array $row): void
    {
        $regularKopecks = $this->moneyToKopecks($row['price'] ?? null);

        CAT_Price::query()->updateOrCreate(
            [
                'item_id' => $item->id,
                'type' => PriceType::Regular->value,
            ],
            [
                'amount_kopecks' => $regularKopecks,
            ],
        );

        $old = $row['old_price'] ?? null;
        if ($old === null || $old === '') {
            CAT_Price::query()
                ->where('item_id', $item->id)
                ->where('type', PriceType::Old->value)
                ->delete();

            return;
        }

        CAT_Price::query()->updateOrCreate(
            [
                'item_id' => $item->id,
                'type' => PriceType::Old->value,
            ],
            [
                'amount_kopecks' => $this->moneyToKopecks($old),
            ],
        );
    }

    /**
     * @param  list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>  $categoryBlocks
     * @param  array<int, CAT_Category>  $categories
     * @param  array<int, CAT_Item>  $products
     */
    private function attachCategoryItems(
        array $categoryBlocks,
        array $categories,
        array $products,
    ): void {
        foreach ($categoryBlocks as $block) {
            $categoryId = (int) ($block['category']['id'] ?? 0);
            $category = $categories[$categoryId] ?? null;
            if ($category === null) {
                continue;
            }

            $sync = [];
            foreach ($block['items'] as $sortOrder => $row) {
                $itemId = (int) ($row['id'] ?? 0);
                $product = $products[$itemId] ?? null;
                if ($product === null) {
                    continue;
                }

                $sync[$product->id] = ['sort_order' => (int) $sortOrder];
            }

            $category->items()->sync($sync);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsById
     * @param  array<string, CAT_Tag>  $tags
     * @param  array<int, CAT_Item>  $products
     */
    private function attachItemTags(array $itemsById, array $tags, array $products): void
    {
        foreach ($itemsById as $itemId => $row) {
            $product = $products[$itemId] ?? null;
            if ($product === null) {
                continue;
            }

            $tagIds = [];
            $rawTags = $row['tags'] ?? [];
            if (is_array($rawTags)) {
                foreach ($rawTags as $tag) {
                    if (! is_array($tag)) {
                        continue;
                    }
                    $code = $this->stringValue($tag['code'] ?? null);
                    if ($code !== '' && isset($tags[$code])) {
                        $tagIds[] = $tags[$code]->id;
                    }
                }
            }

            $product->tags()->sync(array_values(array_unique($tagIds)));
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsById
     * @param  array<int, CAT_Item>  $products
     */
    private function seedSetLines(array $itemsById, array $products): void
    {
        foreach ($itemsById as $setId => $row) {
            if (($row['kind'] ?? null) !== CatalogItemKind::Set->value) {
                continue;
            }

            $set = $products[$setId] ?? null;
            if ($set === null) {
                continue;
            }

            $desired = [];
            $lines = $row['lines'] ?? [];
            if (! is_array($lines)) {
                $lines = [];
            }

            foreach ($lines as $line) {
                if (! is_array($line)) {
                    continue;
                }

                $componentId = (int) ($line['product_id'] ?? 0);
                $quantity = (int) ($line['quantity'] ?? 0);
                if ($componentId <= 0 || $quantity <= 0 || ! isset($products[$componentId])) {
                    continue;
                }

                $desired[$componentId] = $quantity;

                CAT_SetLine::query()->updateOrCreate(
                    [
                        'set_item_id' => $set->id,
                        'component_item_id' => $componentId,
                    ],
                    [
                        'quantity' => $quantity,
                    ],
                );
            }

            CAT_SetLine::query()
                ->where('set_item_id', $set->id)
                ->when(
                    $desired !== [],
                    fn ($query) => $query->whereNotIn('component_item_id', array_keys($desired)),
                    fn ($query) => $query,
                )
                ->delete();
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsById
     * @param  array<int, CAT_Item>  $products
     */
    private function seedPromotionRoles(array $itemsById, array $products): void
    {
        $keepKeys = [];

        foreach ($itemsById as $itemId => $row) {
            $product = $products[$itemId] ?? null;
            if ($product === null) {
                continue;
            }

            $meta = is_array($row['promotion_meta'] ?? null) ? $row['promotion_meta'] : [];
            $regular = CAT_Price::query()
                ->where('item_id', $product->id)
                ->where('type', PriceType::Regular->value)
                ->first();
            $priceSnapshot = $regular instanceof CAT_Price ? (int) $regular->amount_kopecks : 0;

            if (! empty($meta['counts_as_roll'])) {
                $role = ProductPromotionRoleKind::Roll->value;
                PRM_ProductRole::query()->updateOrCreate(
                    [
                        'catalog_item_id' => $product->id,
                        'role' => $role,
                    ],
                    [
                        'name_snapshot' => (string) $product->name,
                        'price_kopecks_snapshot' => $priceSnapshot,
                        'paid_twin_catalog_item_id' => null,
                    ],
                );
                $keepKeys[] = $product->id.':'.$role;
            }

            if (! empty($meta['complement_set'])) {
                $role = ProductPromotionRoleKind::Complement->value;
                $paidTwinId = isset($row['paid_twin_product_id']) && is_numeric($row['paid_twin_product_id'])
                    ? (int) $row['paid_twin_product_id']
                    : null;

                PRM_ProductRole::query()->updateOrCreate(
                    [
                        'catalog_item_id' => $product->id,
                        'role' => $role,
                    ],
                    [
                        'name_snapshot' => (string) $product->name,
                        'price_kopecks_snapshot' => $priceSnapshot,
                        'paid_twin_catalog_item_id' => $paidTwinId,
                    ],
                );
                $keepKeys[] = $product->id.':'.$role;
            }
        }

        if ($keepKeys === []) {
            return;
        }

        $roles = PRM_ProductRole::query()->get();
        foreach ($roles as $roleRow) {
            $key = $roleRow->catalog_item_id.':'.$roleRow->role;
            if (! in_array($key, $keepKeys, true)) {
                $roleRow->delete();
            }
        }
    }

    /**
     * @param  array<int, CAT_Category>  $categories
     * @param  array<int, CAT_Item>  $products
     */
    private function purgeObsoleteRecords(array $categories, array $products): void
    {
        $itemIds = array_keys($products);
        $categoryIds = array_keys($categories);

        CAT_Item::query()
            ->when(
                $itemIds !== [],
                fn ($query) => $query->whereNotIn('id', $itemIds),
                fn ($query) => $query,
            )
            ->delete();

        CAT_Category::query()
            ->when(
                $categoryIds !== [],
                fn ($query) => $query->whereNotIn('id', $categoryIds),
                fn ($query) => $query,
            )
            ->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsById
     * @param  array<int, CAT_Item>  $products
     */
    private function seedItemImages(array $itemsById, array $products): void
    {
        foreach ($products as $itemId => $product) {
            $row = $itemsById[$itemId] ?? null;
            if ($row === null) {
                continue;
            }

            $this->syncItemImages($product, $this->imageDescriptorsFromItemRow($row));
        }
    }

    /**
     * @return array<int, CAT_Item>
     */
    private function itemsIndexedById(): array
    {
        $items = [];
        foreach (CAT_Item::query()->get() as $item) {
            $items[(int) $item->id] = $item;
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<array{url: string, focus: array<string, mixed>|null}>
     */
    private function imageDescriptorsFromItemRow(array $row): array
    {
        $images = $row['images'] ?? [];
        if (! is_array($images)) {
            return [];
        }

        $descriptors = [];

        foreach ($images as $image) {
            if (! is_array($image)) {
                continue;
            }

            $path = $this->largeVariantPath($image);
            if ($path === null) {
                continue;
            }

            $focus = is_array($image['focus'] ?? null) ? $image['focus'] : null;
            $descriptors[] = [
                'url' => $this->absoluteAssetUrl($path),
                'focus' => $focus,
            ];
        }

        return $descriptors;
    }

    /**
     * @param  array<string, mixed>  $image
     */
    private function largeVariantPath(array $image): ?string
    {
        $variants = $image['variants'] ?? [];
        if (! is_array($variants)) {
            return null;
        }

        $bySize = [];
        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $size = $this->stringValue($variant['size'] ?? null);
            $path = $this->stringValue($variant['path'] ?? null);
            if ($size !== '' && $path !== '') {
                $bySize[$size] = $path;
            }
        }

        foreach (['large', 'medium', 'thumb'] as $preferred) {
            if (isset($bySize[$preferred])) {
                return $bySize[$preferred];
            }
        }

        return $bySize === [] ? null : reset($bySize);
    }

    /**
     * @param  list<array{url: string, focus: array<string, mixed>|null}>  $descriptors
     */
    private function syncItemImages(CAT_Item $product, array $descriptors): void
    {
        $disk = Storage::disk('public');
        $storedPaths = [];

        foreach (array_values($descriptors) as $sortOrder => $descriptor) {
            $storagePath = $this->downloadProductImage($product, $descriptor['url'], $disk);
            if ($storagePath === null) {
                continue;
            }

            CAT_ItemImage::query()->updateOrCreate(
                [
                    'item_id' => $product->id,
                    'sort_order' => $sortOrder,
                ],
                [
                    'disk' => 'public',
                    'path' => $storagePath,
                    'focus' => $descriptor['focus'],
                ],
            );

            $storedPaths[] = $storagePath;
        }

        if ($storedPaths === []) {
            CAT_ItemImage::query()->where('item_id', $product->id)->delete();

            return;
        }

        CAT_ItemImage::query()
            ->where('item_id', $product->id)
            ->whereNotIn('path', $storedPaths)
            ->delete();
    }

    private function downloadProductImage(
        CAT_Item $product,
        string $sourceUrl,
        \Illuminate\Contracts\Filesystem\Filesystem $disk,
    ): ?string {
        $basename = basename((string) parse_url($sourceUrl, PHP_URL_PATH));
        if ($basename === '') {
            return null;
        }

        $storagePath = 'catalog/items/'.$product->id.'/'.$basename;
        if ($disk->exists($storagePath)) {
            return $storagePath;
        }

        try {
            $response = Http::timeout(30)
                ->retry(2, 500)
                ->get($sourceUrl);
        } catch (\Throwable $exception) {
            Log::warning('CatalogSeeder: не удалось скачать изображение.', [
                'item_id' => $product->id,
                'url' => $sourceUrl,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('CatalogSeeder: не удалось скачать изображение.', [
                'item_id' => $product->id,
                'url' => $sourceUrl,
                'status' => $response->status(),
            ]);

            return null;
        }

        $disk->put($storagePath, $response->body());

        return $storagePath;
    }

    private function absoluteAssetUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return rtrim(self::CATALOG_SOURCE_ORIGIN, '/').$path;
        }

        if (str_starts_with($path, 'storage/')) {
            return rtrim(self::CATALOG_SOURCE_ORIGIN, '/').'/'.$path;
        }

        return rtrim(self::CATALOG_SOURCE_ORIGIN, '/').'/storage/'.ltrim($path, '/');
    }

    private function moneyToKopecks(mixed $value): int
    {
        if (is_array($value)) {
            $amount = $value['amount'] ?? 0;

            return max(0, (int) round(((float) $amount) * self::KOPECKS_IN_RUBLE));
        }

        if (is_int($value) || is_float($value)) {
            return max(0, (int) round(((float) $value) * self::KOPECKS_IN_RUBLE));
        }

        if (! is_string($value) || $value === '') {
            return 0;
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim($value));

        return max(0, (int) round(((float) $normalized) * self::KOPECKS_IN_RUBLE));
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
