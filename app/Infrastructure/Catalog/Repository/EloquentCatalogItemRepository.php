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
    private const PRICE_TYPE_REGULAR = 'regular';

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
        $rows = $this->itemQuery()
            ->where('kind', CatalogItemKind::Product->value)
            ->with('prices')
            ->orderBy('id')
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

    public function listAllSets(): array
    {
        $rows = $this->itemQuery()
            ->where('kind', CatalogItemKind::Set->value)
            ->with(['setLines', 'prices'])
            ->orderBy('id')
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
        int $price,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
    ): int {
        return $this->saveItem($id, CatalogItemKind::Product, $name, $price, $weight, $status, $description, $sku);
    }

    public function saveSet(
        ?int $id,
        string $name,
        ?string $sku,
        int $price,
        string $status,
    ): int {
        return $this->saveItem($id, CatalogItemKind::Set, $name, $price, null, $status, null, $sku);
    }

    public function listProductImages(int $productId): array
    {
        $this->requireItem($productId);

        return CAT_ItemImage::query()
            ->where('item_id', $productId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (CAT_ItemImage $row): array {
                $path = CatalogStoredImagePath::normalize($row->path) ?? '';
                [$focusX, $focusY] = $this->resolveImageFocus($row);

                return [
                    'id' => (int) $row->id,
                    'url' => $this->imageUrl($path),
                    'focus_x' => $focusX,
                    'focus_y' => $focusY,
                ];
            })
            ->all();
    }

    public function addProductImage(int $productId, string $path): void
    {
        $this->requireItem($productId);
        CAT_ItemImage::query()->create([
            'item_id' => $productId,
            'disk' => 'public',
            'path' => $path,
            'sort_order' => 0,
        ]);
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

    private function saveItem(
        ?int $id,
        CatalogItemKind $kind,
        string $name,
        int $price,
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
        ];
        if ($kind === CatalogItemKind::Product) {
            $fields['weight'] = $weight;
            $fields['description'] = $description;
        }
        $row->fill($fields);
        if ($sku !== null && trim($sku) !== '') {
            $row->sku = trim($sku);
        } elseif ($id === null) {
            $row->sku = $row->slug;
        }
        $row->save();

        CAT_Price::query()->updateOrCreate(
            [
                'item_id' => (int) $row->id,
                'type' => self::PRICE_TYPE_REGULAR,
            ],
            [
                'amount_kopecks' => max(0, $price) * self::KOPECKS_IN_RUBLE,
            ],
        );

        return (int) $row->id;
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
