<?php

namespace App\Infrastructure\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\Category;
use App\Domain\Supporting\Catalog\Entity\CategoryItem;
use App\Domain\Supporting\Catalog\Enum\CatalogItemKind;
use App\Domain\Supporting\Catalog\Repository\CategoryRepository;
use App\Infrastructure\Catalog\Mapper\CatalogCategoryMapper;
use App\Infrastructure\Catalog\Model\CAT_Category;
use App\Infrastructure\Catalog\Model\CAT_CategoryItem;
use App\Infrastructure\Catalog\Model\CAT_Item;
use Illuminate\Support\Str;

final class EloquentCategoryRepository implements CategoryRepository
{
    public function __construct(
        private readonly CatalogCategoryMapper $mapper,
    ) {}

    public function findAllOrdered(): array
    {
        return CAT_Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CAT_Category $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findAllIncludingInactive(): array
    {
        return CAT_Category::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CAT_Category $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function findById(int $id): ?Category
    {
        $row = CAT_Category::query()->find($id);

        return $row instanceof CAT_Category ? $this->mapper->toDomain($row) : null;
    }

    public function findItemsByCategoryId(int $categoryId): array
    {
        $links = CAT_CategoryItem::query()
            ->where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($links->isEmpty()) {
            return [];
        }

        $itemIds = $links
            ->pluck('item_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $kindsById = CAT_Item::query()
            ->whereIn('id', $itemIds)
            ->pluck('kind', 'id');

        $items = [];

        foreach ($links as $link) {
            if (! $link instanceof CAT_CategoryItem) {
                continue;
            }

            $catalogItemId = (int) $link->item_id;
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

    public function save(?int $id, string $name, bool $isActive, bool $isAccompanying): Category
    {
        $row = $id === null ? new CAT_Category() : CAT_Category::query()->find($id);
        if (! $row instanceof CAT_Category) {
            throw new \InvalidArgumentException('Категория не найдена.');
        }

        if ($id === null) {
            $row->slug = $this->uniqueSlug($name);
            $row->sort_order = 0;
        }

        $row->fill([
            'name' => $name,
            'is_active' => $isActive,
            'is_accompanying' => $isAccompanying,
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'category';
        }

        $slug = $base;
        $suffix = 2;
        while (CAT_Category::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
