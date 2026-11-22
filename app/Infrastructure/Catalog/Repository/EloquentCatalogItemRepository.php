<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Entity\ProductSet;
use App\Domain\Supporting\Catalog\Enum\CatalogItemKind;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Catalog\ValueObject\ProductImage;
use App\Infrastructure\Catalog\Mapper\CatalogProductMapper;
use App\Infrastructure\Catalog\Mapper\CatalogProductSetMapper;
use App\Infrastructure\Catalog\Model\CAT_Item;
use App\Infrastructure\Catalog\Model\CAT_ItemImage;
use App\Infrastructure\Catalog\Model\CAT_Price;
use App\Infrastructure\Catalog\Model\CAT_SetLine;
use App\Infrastructure\Catalog\Support\CatalogStoredImagePath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EloquentCatalogItemRepository implements CatalogItemRepository
{
    private const KOPECKS_IN_RUBLE = 100;

    public function __construct(
        private readonly CatalogProductMapper $productMapper,
        private readonly CatalogProductSetMapper $setMapper,
    ) {}

    public function findProductById(int $id): ?Product
    {
        $row = $this->itemQuery()
            ->where('kind', CatalogItemKind::Product->value)
            ->with('prices')
            ->find($id);

        if (! $row instanceof CAT_Item) {
            return null;
        }

        $tagIds = $this->loadTagIdsForItemIds([$id]);
        $images = $this->loadImagesByItemIds([$id]);

        return $this->productMapper->toDomain(
            $row,
            $tagIds[$id] ?? [],
            $images[$id] ?? [],
        );
    }

    public function findSetById(int $id): ?ProductSet
    {
        $row = $this->itemQuery()
            ->where('kind', CatalogItemKind::Set->value)
            ->with(['setLines', 'prices'])
            ->find($id);

        if (! $row instanceof CAT_Item) {
            return null;
        }

        $lines = $row->setLines
            ->map(fn (CAT_SetLine $line) => $this->setMapper->mapLine($line))
            ->all();

        $tagIds = $this->loadTagIdsForItemIds([$id]);
        $images = $this->loadImagesByItemIds([$id]);

        return $this->setMapper->toDomain(
            $row,
            $lines,
            $tagIds[$id] ?? [],
            $images[$id] ?? [],
        );
    }

    public function findActiveProductsByIds(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        $rows = $this->itemQuery()
            ->where('kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->whereNull('archived_at')
            ->whereIn('id', $ids)
            ->with('prices')
            ->get();

        $tagIdsByItem = $this->loadTagIdsForItemIds($ids);
        $imagesByItem = $this->loadImagesByItemIds($ids);

        return $this->mapProductsPreservingOrder($rows, $ids, $tagIdsByItem, $imagesByItem);
    }

    public function findActiveSetsByIds(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        $rows = $this->itemQuery()
            ->where('kind', CatalogItemKind::Set->value)
            ->where('status', ProductStatus::Active->value)
            ->whereNull('archived_at')
            ->whereIn('id', $ids)
            ->with(['setLines', 'prices'])
            ->get();

        $tagIdsBySet = $this->loadTagIdsForItemIds($ids);
        $imagesBySet = $this->loadImagesByItemIds($ids);

        $byId = [];

        foreach ($rows as $row) {
            if (! $row instanceof CAT_Item) {
                continue;
            }

            $lines = $row->setLines
                ->map(fn (CAT_SetLine $line) => $this->setMapper->mapLine($line))
                ->all();

            $setId = (int) $row->id;
            $set = $this->setMapper->toDomain(
                $row,
                $lines,
                $tagIdsBySet[$setId] ?? [],
                $imagesBySet[$setId] ?? [],
            );
            if ($set instanceof ProductSet) {
                $byId[$setId] = $set;
            }
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }

    public function findProductNamesByIds(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        $rows = $this->itemQuery()
            ->where('kind', CatalogItemKind::Product->value)
            ->whereIn('id', $ids)
            ->get(['id', 'name']);

        $names = [];

        foreach ($rows as $row) {
            if (! $row instanceof CAT_Item) {
                continue;
            }

            $name = trim((string) $row->name);
            if ($name === '') {
                continue;
            }

            $names[(int) $row->id] = $name;
        }

        return $names;
    }

    public function listAllProducts(): array
    {
        return $this->listProductsPage(0, max(1, $this->countProducts()));
    }

    public function listProductsPage(int $offset, int $limit, ?string $q = null): array
    {
        $query = $this->itemQuery()
            ->where('kind', CatalogItemKind::Product->value)
            ->with('prices')
            ->orderBy('id');
        $this->applyItemNameSearch($query, $q);

        $rows = $query
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get();

        $ids = $rows
            ->map(static fn (CAT_Item $row): int => (int) $row->id)
            ->all();
        $tagIds = $this->loadTagIdsForItemIds($ids);
        $products = [];

        foreach ($rows as $row) {
            if (! $row instanceof CAT_Item) {
                continue;
            }

            $id = (int) $row->id;
            $products[] = $this->productMapper->toDomain(
                $row,
                $tagIds[$id] ?? [],
                [],
            );
        }

        return $products;
    }

    public function countProducts(?string $q = null): int
    {
        $query = $this->itemQuery()->where('kind', CatalogItemKind::Product->value);
        $this->applyItemNameSearch($query, $q);

        return (int) $query->count();
    }

    public function listAllSets(): array
    {
        return $this->listSetsPage(0, max(1, $this->countSets()));
    }

    public function listSetsPage(int $offset, int $limit, ?string $q = null): array
    {
        $query = $this->itemQuery()
            ->where('kind', CatalogItemKind::Set->value)
            ->with(['setLines', 'prices'])
            ->orderBy('id');
        $this->applyItemNameSearch($query, $q);

        $rows = $query
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get();

        $ids = $rows
            ->map(static fn (CAT_Item $row): int => (int) $row->id)
            ->all();
        $tagIds = $this->loadTagIdsForItemIds($ids);
        $sets = [];

        foreach ($rows as $row) {
            if (! $row instanceof CAT_Item) {
                continue;
            }

            $lines = $row->setLines
                ->map(fn (CAT_SetLine $line) => $this->setMapper->mapLine($line))
                ->all();
            $id = (int) $row->id;
            $set = $this->setMapper->toDomain(
                $row,
                $lines,
                $tagIds[$id] ?? [],
                [],
            );
            if ($set !== null) {
                $sets[] = $set;
            }
        }

        return $sets;
    }

    public function countSets(?string $q = null): int
    {
        $query = $this->itemQuery()->where('kind', CatalogItemKind::Set->value);
        $this->applyItemNameSearch($query, $q);

        return (int) $query->count();
    }

    public function findArchivedProductIds(): array
    {
        return $this->itemQuery()
            ->where('kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Archived->value)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    public function saveProduct(
        ?int $id,
        string $name,
        array $prices,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
        ?array $ingredients = null,
        ?array $nutrition = null,
        ?array $tagIds = null,
        bool $isRoll = false,
        bool $isComplement = false,
    ): int {
        $itemId = $this->saveItem(
            $id,
            CatalogItemKind::Product,
            $name,
            $prices,
            $weight,
            $status,
            $description,
            $sku,
        );

        $row = $this->requireKind($itemId, CatalogItemKind::Product);
        $row->is_roll = $isRoll;
        $row->is_complement = $isComplement;
        if ($ingredients !== null) {
            $row->ingredients = array_values(array_filter(
                array_map(static fn (mixed $v): string => trim((string) $v), $ingredients),
                static fn (string $v): bool => $v !== '',
            ));
        }
        if ($nutrition !== null) {
            $row->nutrition = [
                'calories' => (float) ($nutrition['calories'] ?? 0),
                'proteins' => (float) ($nutrition['proteins'] ?? 0),
                'fats' => (float) ($nutrition['fats'] ?? 0),
                'carbs' => (float) ($nutrition['carbs'] ?? 0),
                'basis' => (string) ($nutrition['basis'] ?? 'per_100g'),
            ];
        }
        $row->save();

        if ($tagIds !== null) {
            $this->syncItemTags($itemId, $tagIds);
        }

        return $itemId;
    }

    public function saveSet(
        ?int $id,
        string $name,
        ?string $sku,
        array $prices,
        string $status,
        ?string $description = null,
    ): int {
        return $this->saveItem($id, CatalogItemKind::Set, $name, $prices, null, $status, $description, $sku);
    }

    public function replaceSetLines(int $setId, array $lines): void
    {
        $this->requireKind($setId, CatalogItemKind::Set);

        $normalized = [];
        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }
            $productId = (int) ($line['product_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            if ($productId < 1 || $quantity < 1) {
                continue;
            }
            $normalized[$productId] = $quantity;
        }

        if ($normalized !== []) {
            $existing = CAT_Item::query()
                ->where('kind', CatalogItemKind::Product->value)
                ->whereIn('id', array_keys($normalized))
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->all();
            $missing = array_diff(array_keys($normalized), $existing);
            if ($missing !== []) {
                throw new \InvalidArgumentException('В набор можно добавлять только существующие товары: '.implode(', ', $missing));
            }
        }

        CAT_SetLine::query()->where('set_item_id', $setId)->delete();
        foreach ($normalized as $productId => $quantity) {
            CAT_SetLine::query()->create([
                'set_item_id' => $setId,
                'component_item_id' => $productId,
                'quantity' => $quantity,
            ]);
        }
    }

    public function listItemImages(int $itemId): array
    {
        $this->requireItem($itemId);

        return CAT_ItemImage::query()
            ->where('item_id', $itemId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (CAT_ItemImage $row): array {
                $path = CatalogStoredImagePath::normalize($row->path) ?? '';
                [$focusX, $focusY] = $this->resolveImageFocus($row);

                return [
                    'id' => (int) $row->id,
                    'url' => $this->imageUrl($path),
                    'path' => $path,
                    'sort_order' => (int) $row->sort_order,
                    'focus_x' => $focusX,
                    'focus_y' => $focusY,
                ];
            })
            ->all();
    }

    public function listProductImages(int $productId): array
    {
        return array_map(
            static fn (array $image): array => [
                'id' => $image['id'],
                'url' => $image['url'],
                'focus_x' => $image['focus_x'],
                'focus_y' => $image['focus_y'],
            ],
            $this->listItemImages($productId),
        );
    }

    public function addProductImage(int $productId, string $path): void
    {
        $this->addItemImage($productId, $path);
    }

    public function addItemImage(int $itemId, string $path, ?float $focusX = null, ?float $focusY = null): array
    {
        $this->requireItem($itemId);
        $normalized = CatalogStoredImagePath::normalize($path);
        if ($normalized === null) {
            throw new \InvalidArgumentException('Некорректный путь изображения.');
        }

        $maxSort = (int) CAT_ItemImage::query()->where('item_id', $itemId)->max('sort_order');
        $focus = null;
        if ($focusX !== null && $focusY !== null) {
            $focus = ['x' => $focusX, 'y' => $focusY];
        }

        $row = CAT_ItemImage::query()->create([
            'item_id' => $itemId,
            'disk' => 'public',
            'path' => $normalized,
            'sort_order' => $maxSort + 1,
            'focus' => $focus,
        ]);

        return $this->presentImageRow($row);
    }

    public function updateItemImage(
        int $itemId,
        int $imageId,
        ?float $focusX = null,
        ?float $focusY = null,
        ?int $sortOrder = null,
    ): array {
        $row = CAT_ItemImage::query()
            ->where('item_id', $itemId)
            ->whereKey($imageId)
            ->first();
        if (! $row instanceof CAT_ItemImage) {
            throw new \InvalidArgumentException('Изображение не найдено.');
        }

        if ($focusX !== null && $focusY !== null) {
            $row->focus = ['x' => $focusX, 'y' => $focusY];
        }
        if ($sortOrder !== null) {
            $row->sort_order = $sortOrder;
        }
        $row->save();

        return $this->presentImageRow($row);
    }

    public function deleteItemImage(int $itemId, int $imageId): void
    {
        $row = CAT_ItemImage::query()
            ->where('item_id', $itemId)
            ->whereKey($imageId)
            ->first();
        if (! $row instanceof CAT_ItemImage) {
            throw new \InvalidArgumentException('Изображение не найдено.');
        }

        $path = CatalogStoredImagePath::normalize($row->path);
        $row->delete();
        if ($path !== null && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function deleteProduct(int $id): void
    {
        $row = $this->requireKind($id, CatalogItemKind::Product);
        if ((bool) $row->is_system) {
            throw new \InvalidArgumentException('Системный товар нельзя удалить.');
        }
        $this->deleteCatalogItem($row);
    }

    public function deleteSet(int $id): void
    {
        $row = $this->requireKind($id, CatalogItemKind::Set);
        $this->deleteCatalogItem($row);
    }

    private function deleteCatalogItem(CAT_Item $row): void
    {
        $itemId = (int) $row->id;
        $images = CAT_ItemImage::query()->where('item_id', $itemId)->get();
        $paths = [];
        foreach ($images as $image) {
            if (! $image instanceof CAT_ItemImage) {
                continue;
            }
            $path = CatalogStoredImagePath::normalize($image->path);
            if ($path !== null) {
                $paths[] = $path;
            }
        }

        $row->delete();

        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function itemQuery()
    {
        return CAT_Item::query();
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            if (! is_numeric($id)) {
                continue;
            }

            $intId = (int) $id;
            if ($intId > 0) {
                $normalized[] = $intId;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param  list<int>  $itemIds
     * @return array<int, list<int>>
     */
    private function loadTagIdsForItemIds(array $itemIds): array
    {
        $itemIds = $this->normalizeIds($itemIds);
        if ($itemIds === []) {
            return [];
        }

        $rows = DB::table('CAT_item_tags')
            ->whereIn('item_id', $itemIds)
            ->orderBy('tag_id')
            ->get(['item_id', 'tag_id']);

        $map = [];

        foreach ($rows as $row) {
            $itemId = (int) $row->item_id;
            $map[$itemId] ??= [];
            $map[$itemId][] = (int) $row->tag_id;
        }

        return $map;
    }

    /**
     * @param  list<int>  $itemIds
     * @return array<int, list<ProductImage>>
     */
    private function loadImagesByItemIds(array $itemIds): array
    {
        $itemIds = $this->normalizeIds($itemIds);
        if ($itemIds === []) {
            return [];
        }

        $rows = CAT_ItemImage::query()
            ->whereIn('item_id', $itemIds)
            ->orderBy('item_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $map = [];

        foreach ($rows as $row) {
            if (! $row instanceof CAT_ItemImage) {
                continue;
            }

            $path = CatalogStoredImagePath::normalize($row->path);
            if ($path === null) {
                continue;
            }

            $itemId = (int) $row->item_id;
            $map[$itemId] ??= [];
            [$focusX, $focusY] = $this->resolveImageFocus($row);
            $map[$itemId][] = new ProductImage(
                path: $path,
                sortOrder: (int) $row->sort_order,
                focusX: $focusX,
                focusY: $focusY,
            );
        }

        return $map;
    }

    /**
     * @param  iterable<CAT_Item>  $rows
     * @param  list<int>  $order
     * @param  array<int, list<int>>  $tagIdsByItem
     * @param  array<int, list<ProductImage>>  $imagesByItem
     * @return list<Product>
     */
    private function mapProductsPreservingOrder(
        iterable $rows,
        array $order,
        array $tagIdsByItem,
        array $imagesByItem,
    ): array {
        $byId = [];

        foreach ($rows as $row) {
            if (! $row instanceof CAT_Item) {
                continue;
            }

            $id = (int) $row->id;
            $byId[$id] = $this->productMapper->toDomain(
                $row,
                $tagIdsByItem[$id] ?? [],
                $imagesByItem[$id] ?? [],
            );
        }

        $ordered = [];
        foreach ($order as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveImageFocus(CAT_ItemImage $row): array
    {
        $focus = is_array($row->focus) ? $row->focus : null;
        if ($focus === null) {
            return [null, null];
        }

        $rawX = $focus['x'] ?? null;
        $rawY = $focus['y'] ?? null;

        if ($rawX === null || $rawY === null) {
            return [null, null];
        }

        $focusX = (float) $rawX;
        $focusY = (float) $rawY;

        if ($focusX < 0.0 || $focusX > 100.0 || $focusY < 0.0 || $focusY > 100.0) {
            return [null, null];
        }

        return [$focusX, $focusY];
    }

    /**
     * @param  list<array{type: string, amount: int}>  $prices
     */
    private function saveItem(
        ?int $id,
        CatalogItemKind $kind,
        string $name,
        array $prices,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
    ): int {
        if (ProductStatus::tryFrom($status) === null) {
            throw new \InvalidArgumentException('Недопустимый статус.');
        }

        $row = $id === null ? new CAT_Item() : $this->requireKind($id, $kind);
        if ($id === null) {
            $row->slug = $this->uniqueItemSlug($name);
            $row->kind = $kind->value;
            $row->is_system = false;
        }

        $fields = [
            'name' => $name,
            'status' => $status,
            'description' => $description,
            'archived_at' => $status === ProductStatus::Archived->value ? ($row->archived_at ?? now()) : null,
        ];
        if ($kind === CatalogItemKind::Product) {
            $fields['weight'] = $weight;
        }
        $row->fill($fields);
        if ($sku !== null && trim($sku) !== '') {
            $row->sku = trim($sku);
        } elseif ($id === null) {
            $row->sku = $row->slug;
        }
        $row->save();

        $this->syncPrices((int) $row->id, $prices);

        return (int) $row->id;
    }

    /**
     * @param  list<array{type: string, amount: int}>  $prices
     */
    private function syncPrices(int $itemId, array $prices): void
    {
        $keptTypes = [];
        foreach ($prices as $price) {
            $type = (string) ($price['type'] ?? '');
            if ($type === '') {
                continue;
            }
            $keptTypes[] = $type;
            CAT_Price::query()->updateOrCreate(
                [
                    'item_id' => $itemId,
                    'type' => $type,
                ],
                [
                    'amount_kopecks' => max(0, (int) ($price['amount'] ?? 0)) * self::KOPECKS_IN_RUBLE,
                ],
            );
        }

        $query = CAT_Price::query()->where('item_id', $itemId);
        if ($keptTypes !== []) {
            $query->whereNotIn('type', $keptTypes);
        }
        $query->delete();
    }

    /**
     * @param  list<int>  $tagIds
     */
    private function syncItemTags(int $itemId, array $tagIds): void
    {
        $normalized = [];
        foreach ($tagIds as $tagId) {
            if (! is_numeric($tagId)) {
                continue;
            }
            $id = (int) $tagId;
            if ($id > 0) {
                $normalized[] = $id;
            }
        }
        $normalized = array_values(array_unique($normalized));

        CAT_Item::query()->findOrFail($itemId)->tags()->sync($normalized);
    }

    /**
     * @return array{id: int, url: string, path: string, sort_order: int, focus_x: float|null, focus_y: float|null}
     */
    private function presentImageRow(CAT_ItemImage $row): array
    {
        $path = CatalogStoredImagePath::normalize($row->path) ?? '';
        [$focusX, $focusY] = $this->resolveImageFocus($row);

        return [
            'id' => (int) $row->id,
            'url' => $this->imageUrl($path),
            'path' => $path,
            'sort_order' => (int) $row->sort_order,
            'focus_x' => $focusX,
            'focus_y' => $focusY,
        ];
    }

    private function requireKind(int $id, CatalogItemKind $kind): CAT_Item
    {
        $row = CAT_Item::query()
            ->whereKey($id)
            ->where('kind', $kind->value)
            ->first();
        if (! $row instanceof CAT_Item) {
            throw new \InvalidArgumentException($kind === CatalogItemKind::Set ? 'Набор не найден.' : 'Товар не найден.');
        }

        return $row;
    }

    private function requireItem(int $itemId): CAT_Item
    {
        $row = CAT_Item::query()->find($itemId);
        if (! $row instanceof CAT_Item) {
            throw new \InvalidArgumentException('Товар не найден.');
        }

        return $row;
    }

    private function applyItemNameSearch(mixed $query, ?string $q): void
    {
        $needle = trim((string) ($q ?? ''));
        if ($needle === '') {
            return;
        }
        $query->where('name', 'like', '%'.$needle.'%');
    }

    private function uniqueItemSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $suffix = 2;
        while (CAT_Item::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function imageUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        return Storage::disk('public')->url(ltrim($path, '/'));
    }
}
