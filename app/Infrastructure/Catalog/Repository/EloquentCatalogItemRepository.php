<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Catalog\ValueObject\ProductImage;
use App\Infrastructure\Catalog\Mapper\CatalogProductMapper;
use App\Infrastructure\Catalog\Model\CAT_Item;
use App\Infrastructure\Catalog\Model\CAT_ItemImage;
use App\Infrastructure\Catalog\Support\CatalogStoredImagePath;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EloquentCatalogItemRepository implements CatalogItemRepository
{
    public function __construct(
        private readonly CatalogProductMapper $productMapper,
    ) {}

    public function findProductById(int $id): ?Product
    {
        $row = CAT_Item::query()->find($id);

        if (! $row instanceof CAT_Item) {
            return null;
        }

        $images = $this->loadImagesByItemIds([$id]);

        return $this->productMapper->toDomain($row, $images[$id] ?? []);
    }

    public function findActiveProductsByIds(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        $rows = CAT_Item::query()
            ->where('status', ProductStatus::Active->value)
            ->whereNull('archived_at')
            ->whereIn('id', $ids)
            ->get();

        $imagesByItem = $this->loadImagesByItemIds($ids);

        return $this->mapProductsPreservingOrder($rows, $ids, $imagesByItem);
    }

    public function findProductNamesByIds(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        $rows = CAT_Item::query()
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

    public function findArchivedProductIds(): array
    {
        return CAT_Item::query()
            ->where('status', ProductStatus::Archived->value)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    public function listAllProducts(): array
    {
        return $this->listProductsPage(0, max(1, $this->countProducts()));
    }

    public function listProductsPage(int $offset, int $limit, ?string $q = null): array
    {
        $query = CAT_Item::query()->orderBy('id');
        $this->applyItemNameSearch($query, $q);

        $rows = $query
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get();

        $products = [];

        foreach ($rows as $row) {
            if (! $row instanceof CAT_Item) {
                continue;
            }

            $products[] = $this->productMapper->toDomain($row);
        }

        return $products;
    }

    public function countProducts(?string $q = null): int
    {
        $query = CAT_Item::query();
        $this->applyItemNameSearch($query, $q);

        return (int) $query->count();
    }

    public function saveProduct(
        ?int $id,
        string $name,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
        ?array $ingredients = null,
        ?array $nutrition = null,
        bool $isRoll = false,
    ): int {
        if (ProductStatus::tryFrom($status) === null) {
            throw new \InvalidArgumentException('Недопустимый статус.');
        }

        $row = $id === null ? new CAT_Item() : $this->requireItem($id);
        if ($id === null) {
            $row->slug = $this->uniqueItemSlug($name);
            $row->is_system = false;
        }

        $row->fill([
            'name' => $name,
            'status' => $status,
            'description' => $description,
            'weight' => $weight,
            'is_roll' => $isRoll,
            'archived_at' => $status === ProductStatus::Archived->value ? ($row->archived_at ?? now()) : null,
        ]);

        if ($sku !== null && trim($sku) !== '') {
            $row->sku = trim($sku);
        } elseif ($id === null) {
            $row->sku = $row->slug;
        }

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

        return (int) $row->id;
    }

    public function listItemImages(int $itemId): array
    {
        $this->requireItem($itemId);

        return CAT_ItemImage::query()
            ->where('item_id', $itemId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CAT_ItemImage $row): array => $this->presentImageRow($row))
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
        $row = $this->requireItem($id);
        if ((bool) $row->is_system) {
            throw new \InvalidArgumentException('Системный товар нельзя удалить.');
        }

        $paths = [];
        $images = CAT_ItemImage::query()->where('item_id', $id)->get();
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
     * @param  array<int, list<ProductImage>>  $imagesByItem
     * @return list<Product>
     */
    private function mapProductsPreservingOrder(iterable $rows, array $order, array $imagesByItem): array
    {
        $byId = [];

        foreach ($rows as $row) {
            if (! $row instanceof CAT_Item) {
                continue;
            }

            $id = (int) $row->id;
            $byId[$id] = $this->productMapper->toDomain($row, $imagesByItem[$id] ?? []);
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
