<?php

namespace Database\Seeders;

use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Infrastructure\Catalog\Model\CAT_Item;
use App\Infrastructure\Catalog\Model\CAT_ItemImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Товары каталога из публичного API витрины → CAT_items + CAT_item_images.
 * is_roll берётся из promotion_meta; роли комплемента/подарка задаются категориями филиала.
 *
 * Категории, теги, сеты и цены сюда НЕ входят: структура меню и цены — в филиале (CMP_*),
 * наполняет админка. Позиции API с kind=set пропускаются (сет — сущность филиала).
 */
class CatalogSeeder extends Seeder
{
    private const CATALOG_SOURCE_URL = 'https://gangsta-sushi.ru/api/catalog';

    private const CATALOG_SOURCE_ORIGIN = 'https://gangsta-sushi.ru';

    public function run(): void
    {
        $payload = $this->fetchCatalogPayload();
        $categoryBlocks = $this->categoryBlocksFromPayload($payload);
        $itemsById = $this->collectProductRowsById($payload, $categoryBlocks);

        DB::transaction(function () use ($itemsById): void {
            $products = $this->seedItems($itemsById);
            $this->purgeObsoleteItems($products);
            $this->seedItemImages($itemsById, $products);
        });
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
    private function collectProductRowsById(array $payload, array $categoryBlocks): array
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
        if ($id <= 0 || ($item['kind'] ?? null) === 'set') {
            return;
        }

        $itemsById[$id] = $item;
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsById
     * @return array<int, CAT_Item>
     */
    private function seedItems(array $itemsById): array
    {
        $products = [];

        foreach ($itemsById as $id => $row) {
            $statusRaw = $this->stringValue($row['status'] ?? ProductStatus::Active->value);
            $status = $statusRaw === ProductStatus::Archived->value
                ? ProductStatus::Archived->value
                : ProductStatus::Active->value;

            $slug = $this->stringValue($row['slug'] ?? null);
            if ($slug === '') {
                $slug = 'item-'.$id;
            }

            $meta = is_array($row['promotion_meta'] ?? null) ? $row['promotion_meta'] : [];

            $item = CAT_Item::query()->updateOrCreate(
                ['id' => $id],
                [
                    'name' => $this->stringValue($row['name'] ?? $slug),
                    'slug' => $slug,
                    'sku' => (string) $id,
                    'description' => $this->nullableString($row['description'] ?? null),
                    'status' => $status,
                    'is_system' => false,
                    'is_roll' => ! empty($meta['counts_as_roll']),
                    'weight' => isset($row['weight']) && is_numeric($row['weight'])
                        ? (int) $row['weight']
                        : null,
                    'nutrition' => is_array($row['nutrition'] ?? null) ? $row['nutrition'] : null,
                    'ingredients' => is_array($row['ingredients'] ?? null) ? $row['ingredients'] : null,
                    'archived_at' => $status === ProductStatus::Archived->value ? now() : null,
                ],
            );

            $products[$id] = $item;
        }

        return $products;
    }

    /**
     * @param  array<int, CAT_Item>  $products
     */
    private function purgeObsoleteItems(array $products): void
    {
        $itemIds = array_keys($products);

        CAT_Item::query()
            ->when(
                $itemIds !== [],
                fn ($query) => $query->whereNotIn('id', $itemIds),
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
