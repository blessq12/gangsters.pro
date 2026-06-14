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
use App\Infrastructure\Catalog\Model\PRD_ProductSetLine;

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

        return $this->productMapper->toDomain($row, $tagIds[$id] ?? []);
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

        return $this->setMapper->toDomain($row, $lines, $tagIds[$id] ?? []);
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
            ->get();

        $tagIdsByProduct = $this->loadTagIdsForProductIds($ids);

        return $this->mapProductsPreservingOrder($rows, $ids, $tagIdsByProduct);
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

        $byId = [];

        foreach ($rows as $row) {
            if (! $row instanceof PRD_Product) {
                continue;
            }

            $lines = $row->setLines
                ->map(fn (PRD_ProductSetLine $line) => $this->setMapper->mapLine($line))
                ->all();

            $set = $this->setMapper->toDomain($row, $lines, $tagIdsBySet[(int) $row->id] ?? []);
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
     * @param  iterable<PRD_Product>  $rows
     * @param  list<int>  $order
     * @param  array<int, list<int>>  $tagIdsByProduct
     * @return list<Product>
     */
    private function mapProductsPreservingOrder(iterable $rows, array $order, array $tagIdsByProduct): array
    {
        $byId = [];

        foreach ($rows as $row) {
            if (! $row instanceof PRD_Product) {
                continue;
            }

            $id = (int) $row->id;
            $byId[$id] = $this->productMapper->toDomain($row, $tagIdsByProduct[$id] ?? []);
        }

        $ordered = [];
        foreach ($order as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }
}
