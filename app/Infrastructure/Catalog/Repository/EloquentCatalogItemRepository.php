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

        $rows = $this->productQuery()
            ->where('catalog_kind', CatalogItemKind::Product->value)
            ->where('status', ProductStatus::Active->value)
            ->whereNull('archived_at')
            ->whereIn('id', $ids)
            ->where(function ($query): void {
                $query->where('is_system', false)
                    ->orWhere('meta_is_complement_set', true);
            })
            ->get();

        $tagIdsByProduct = $this->loadTagIdsForProductIds($ids);
        $imagesByProduct = $this->loadImagesByProductIds($ids);

        return $this->mapProductsPreservingOrder($rows, $ids, $tagIdsByProduct, $imagesByProduct);
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
            $map[$productId][] = new ProductImage(
                path: $path,
                sortOrder: (int) $row->sort_order,
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
}
