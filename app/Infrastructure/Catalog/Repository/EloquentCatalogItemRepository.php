<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Infrastructure\Catalog\Mapper\CatalogProductMapper;
use App\Infrastructure\Catalog\Mapper\CatalogProductSetMapper;
use App\Infrastructure\Catalog\Model\PRD_Product;
use App\Infrastructure\Catalog\Model\PRD_ProductImage;
use App\Infrastructure\Catalog\Model\PRD_ProductSetLine;
use App\Infrastructure\Catalog\Support\CatalogStoredImagePath;
use App\Domain\Catalog\ValueObject\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EloquentCatalogItemRepository implements CatalogItemRepository
{
    public function __construct(
        private readonly CatalogProductMapper $productMapper,
        private readonly CatalogProductSetMapper $setMapper,
    ) {}

    public function findProductById(int $id): ?Product
    {
        $row = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->find($id);

        if (! $row instanceof PRD_Product) {
            return null;
        }

        $tagIds = $this->loadTagIdsForProductIds([$id]);
        $images = $this->loadImagesByProductIds([$id]);

        return $this->productMapper->toDomain(
            $row,
            $tagIds[$id] ?? [],
            $images[$id] ?? [],
        );
    }

    public function findSetById(int $id): ?ProductSet
    {
        $row = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Set->value)
            ->with('setLines')
            ->find($id);

        if (! $row instanceof PRD_Product) {
            return null;
        }

        $lines = $row->setLines
            ->map(fn (PRD_ProductSetLine $line) => $this->setMapper->mapLine($line))
            ->all();

        $tagIds = $this->loadTagIdsForProductIds([$id]);
        $images = $this->loadImagesByProductIds([$id]);

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

        $paidTwinIds = $this->findPaidTwinProductIds();

        $rows = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->whereNull('archived_at')
            ->whereIn('id', $ids)
            ->where(function ($query) use ($paidTwinIds): void {
                $query->where('is_system', false)
                    ->orWhere('meta_is_complement_set', true);

                if ($paidTwinIds !== []) {
                    $query->orWhereIn('id', $paidTwinIds);
                }
            })
            ->get();

        $tagIdsByProduct = $this->loadTagIdsForProductIds($ids);
        $imagesByProduct = $this->loadImagesByProductIds($ids);

        return $this->mapProductsPreservingOrder($rows, $ids, $tagIdsByProduct, $imagesByProduct);
    }

    public function findPaidTwinProductIds(): array
    {
        $ids = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('meta_is_complement_set', true)
            ->whereNotNull('paid_twin_product_id')
            ->pluck('paid_twin_product_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        return $ids;
    }

    public function findActiveSystemProducts(): array
    {
        $rows = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->where('is_system', true)
            ->whereNull('archived_at')
            ->orderBy('id')
            ->get();

        $ids = [];
        foreach ($rows as $row) {
            if ($row instanceof PRD_Product) {
                $ids[] = (int) $row->id;
            }
        }

        if ($ids === []) {
            return [];
        }

        $tagIdsByProduct = $this->loadTagIdsForProductIds($ids);
        $imagesByProduct = $this->loadImagesByProductIds($ids);

        return $this->mapProductsPreservingOrder($rows, $ids, $tagIdsByProduct, $imagesByProduct);
    }

    public function findActiveComplementSetProducts(): array
    {
        $rows = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->where('meta_is_complement_set', true)
            ->whereNull('archived_at')
            ->orderBy('id')
            ->get();

        $ids = [];
        foreach ($rows as $row) {
            if ($row instanceof PRD_Product) {
                $ids[] = (int) $row->id;
            }
        }

        if ($ids === []) {
            return [];
        }

        $tagIdsByProduct = $this->loadTagIdsForProductIds($ids);
        $imagesByProduct = $this->loadImagesByProductIds($ids);

        return $this->mapProductsPreservingOrder($rows, $ids, $tagIdsByProduct, $imagesByProduct);
    }

    public function findActiveSetsByIds(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        $rows = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Set->value)
            ->where('status', ProductStatus::Active->value)
            ->whereNull('archived_at')
            ->whereIn('id', $ids)
            ->with('setLines')
            ->get();

        $tagIdsBySet = $this->loadTagIdsForProductIds($ids);
        $imagesBySet = $this->loadImagesByProductIds($ids);

        $byId = [];

        foreach ($rows as $row) {
            if (! $row instanceof PRD_Product) {
                continue;
            }

            $lines = $row->setLines
                ->map(fn (PRD_ProductSetLine $line) => $this->setMapper->mapLine($line))
                ->all();

            $setId = (int) $row->id;
            $set = $this->setMapper->toDomain(
                $row,
                $lines,
                $tagIdsBySet[$setId] ?? [],
                $imagesBySet[$setId] ?? [],
            );
            if ($set instanceof ProductSet) {
                $byId[(int) $row->id] = $set;
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

        $rows = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->whereIn('id', $ids)
            ->get(['id', 'name']);

        $names = [];

        foreach ($rows as $row) {
            if (! $row instanceof PRD_Product) {
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
        $rows = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->orderBy('id')
            ->get();

        $ids = $rows
            ->map(static fn (PRD_Product $row): int => (int) $row->id)
            ->all();
        $tagIds = $this->loadTagIdsForProductIds($ids);
        $products = [];

        foreach ($rows as $row) {
            if (! $row instanceof PRD_Product) {
                continue;
            }

            $id = (int) $row->id;
            $products[] = $this->productMapper->toDomain($row, $tagIds[$id] ?? [], []);
        }

        return $products;
    }

    public function listAllSets(): array
    {
        $rows = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Set->value)
            ->with('setLines')
            ->orderBy('id')
            ->get();

        $ids = $rows
            ->map(static fn (PRD_Product $row): int => (int) $row->id)
            ->all();
        $tagIds = $this->loadTagIdsForProductIds($ids);
        $sets = [];

        foreach ($rows as $row) {
            if (! $row instanceof PRD_Product) {
                continue;
            }

            $lines = $row->setLines
                ->map(fn (PRD_ProductSetLine $line) => $this->setMapper->mapLine($line))
                ->all();
            $id = (int) $row->id;
            $set = $this->setMapper->toDomain($row, $lines, $tagIds[$id] ?? [], []);
            if ($set !== null) {
                $sets[] = $set;
            }
        }

        return $sets;
    }

    public function findArchivedProductIds(): array
    {
        return $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Archived->value)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    private function productQuery()
    {
        return PRD_Product::query();
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
     * @param  list<int>  $productIds
     * @return array<int, list<int>>
     */
    private function loadTagIdsForProductIds(array $productIds): array
    {
        $productIds = $this->normalizeIds($productIds);
        if ($productIds === []) {
            return [];
        }

        $rows = \Illuminate\Support\Facades\DB::table('PRD_product_tag')
            ->whereIn('product_id', $productIds)
            ->orderBy('tag_id')
            ->get(['product_id', 'tag_id']);

        $map = [];

        foreach ($rows as $row) {
            $productId = (int) $row->product_id;
            $map[$productId] ??= [];
            $map[$productId][] = (int) $row->tag_id;
        }

        return $map;
    }

    /**
     * @param  list<int>  $productIds
     * @return array<int, list<ProductImage>>
     */
    private function loadImagesByProductIds(array $productIds): array
    {
        $productIds = $this->normalizeIds($productIds);
        if ($productIds === []) {
            return [];
        }

        $rows = PRD_ProductImage::query()
            ->whereIn('product_id', $productIds)
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $map = [];

        foreach ($rows as $row) {
            if (! $row instanceof PRD_ProductImage) {
                continue;
            }

            $path = CatalogStoredImagePath::normalize($row->path);
            if ($path === null) {
                continue;
            }

            $productId = (int) $row->product_id;
            $map[$productId] ??= [];
            [$focusX, $focusY] = $this->resolveImageFocus($row);
            $map[$productId][] = new ProductImage(
                path: $path,
                sortOrder: (int) $row->sort_order,
                focusX: $focusX,
                focusY: $focusY,
            );
        }

        return $map;
    }

    /**
     * @param  iterable<PRD_Product>  $rows
     * @param  list<int>  $order
     * @param  array<int, list<int>>  $tagIdsByProduct
     * @param  array<int, list<ProductImage>>  $imagesByProduct
     * @return list<Product>
     */
    private function mapProductsPreservingOrder(
        iterable $rows,
        array $order,
        array $tagIdsByProduct,
        array $imagesByProduct,
    ): array {
        $byId = [];

        foreach ($rows as $row) {
            if (! $row instanceof PRD_Product) {
                continue;
            }

            $id = (int) $row->id;
            $byId[$id] = $this->productMapper->toDomain(
                $row,
                $tagIdsByProduct[$id] ?? [],
                $imagesByProduct[$id] ?? [],
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
     * @param  list<int>  $ids
     * @return array<int, array{counts_as_roll: bool, complement_set: bool}>
     */
    public function findPromotionMetaByProductIds(array $ids): array
    {
        $ids = $this->normalizeIds($ids);
        if ($ids === []) {
            return [];
        }

        return PRD_Product::query()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->whereIn('id', $ids)
            ->get(['id', 'meta_counts_as_roll', 'meta_is_complement_set'])
            ->mapWithKeys(static fn (PRD_Product $row): array => [
                (int) $row->id => [
                    'counts_as_roll' => (bool) $row->meta_counts_as_roll,
                    'complement_set' => (bool) $row->meta_is_complement_set,
                ],
            ])
            ->all();
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveImageFocus(PRD_ProductImage $row): array
    {
        $rawX = $row->focus_x;
        $rawY = $row->focus_y;

        if ($rawX === null && $rawY === null) {
            return [null, null];
        }

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

    public function saveProduct(
        ?int $id,
        string $name,
        int $price,
        ?int $oldPrice,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
    ): int {
        return $this->saveItem($id, CatalogItemKind::Product, $name, $price, $oldPrice, $weight, $status, $description, $sku);
    }

    public function saveSet(
        ?int $id,
        string $name,
        ?string $sku,
        int $price,
        ?int $oldPrice,
        string $status,
    ): int {
        return $this->saveItem($id, CatalogItemKind::Set, $name, $price, $oldPrice, null, $status, null, $sku);
    }

    public function listProductImages(int $productId): array
    {
        $this->requireItem($productId);

        return PRD_ProductImage::query()
            ->where('product_id', $productId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (PRD_ProductImage $row): array {
                $path = CatalogStoredImagePath::normalize($row->path) ?? '';

                return [
                    'id' => (int) $row->id,
                    'url' => $this->imageUrl($path),
                    'focus_x' => $row->focus_x !== null ? (float) $row->focus_x : null,
                    'focus_y' => $row->focus_y !== null ? (float) $row->focus_y : null,
                ];
            })
            ->all();
    }

    public function addProductImage(int $productId, string $path): void
    {
        $this->requireItem($productId);
        PRD_ProductImage::query()->create([
            'product_id' => $productId,
            'disk' => 'public',
            'path' => $path,
        ]);
    }

    private function saveItem(
        ?int $id,
        CatalogItemKind $kind,
        string $name,
        int $price,
        ?int $oldPrice,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
    ): int {
        if (ProductStatus::tryFrom($status) === null) {
            throw new \InvalidArgumentException('Недопустимый статус.');
        }

        $row = $id === null ? new PRD_Product() : $this->requireKind($id, $kind);
        if ($id === null) {
            $row->slug = $this->uniqueProductSlug($name);
            $row->catalog_kind = $kind->value;
            $row->is_system = false;
        }

        $fields = [
            'name' => $name,
            'price' => $price,
            'old_price' => $oldPrice,
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

        return (int) $row->id;
    }

    private function requireKind(int $id, CatalogItemKind $kind): PRD_Product
    {
        $row = PRD_Product::query()
            ->whereKey($id)
            ->where('catalog_kind', $kind->value)
            ->first();
        if (! $row instanceof PRD_Product) {
            throw new \InvalidArgumentException($kind === CatalogItemKind::Set ? 'Набор не найден.' : 'Товар не найден.');
        }

        return $row;
    }

    private function requireItem(int $productId): PRD_Product
    {
        $row = PRD_Product::query()->find($productId);
        if (! $row instanceof PRD_Product) {
            throw new \InvalidArgumentException('Товар не найден.');
        }

        return $row;
    }

    private function uniqueProductSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $suffix = 2;
        while (PRD_Product::query()->where('slug', $slug)->exists()) {
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
