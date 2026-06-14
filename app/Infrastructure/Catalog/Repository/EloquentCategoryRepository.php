<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Domain\Catalog\Entity\Category;
use App\Domain\Catalog\Entity\CategoryItem;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Repository\CategoryRepository;
use App\Infrastructure\Catalog\Mapper\CatalogCategoryMapper;
use App\Infrastructure\Catalog\Model\PRD_Category;
use App\Infrastructure\Catalog\Model\PRD_CategoryProduct;
use App\Infrastructure\Catalog\Model\PRD_Product;

final class EloquentCategoryRepository implements CategoryRepository
{
    public function __construct(
        private readonly CatalogCategoryMapper $mapper,
    ) {}

    public function findAllOrdered(): array
    {
        return PRD_Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PRD_Category $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findById(int $id): ?Category
    {
        $row = PRD_Category::query()->find($id);

        return $row instanceof PRD_Category ? $this->mapper->toDomain($row) : null;
    }

    public function findItemsByCategoryId(int $categoryId): array
    {
        $links = PRD_CategoryProduct::query()
            ->where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($links->isEmpty()) {
            return [];
        }

        $productIds = $links
            ->pluck('product_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $kindsById = PRD_Product::query()
            ->whereIn('id', $productIds)
            ->pluck('catalog_kind', 'id');

        $items = [];

        foreach ($links as $link) {
            if (! $link instanceof PRD_CategoryProduct) {
                continue;
            }

            $catalogItemId = (int) $link->product_id;
            $kindValue = (string) ($kindsById[$catalogItemId] ?? CatalogItemKind::Product->value);
            $kind = CatalogItemKind::tryFrom($kindValue) ?? CatalogItemKind::Product;

            $items[] = new CategoryItem(
                categoryId: $categoryId,
                catalogItemId: $catalogItemId,
                kind: $kind,
                sortOrder: (int) $link->sort_order,
            );
        }

        return $items;
    }
}
