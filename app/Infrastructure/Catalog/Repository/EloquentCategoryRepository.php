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

    public function listPage(int $offset, int $limit): array
    {
        return CAT_Category::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (CAT_Category $row) => $this->mapper->toDomain($row))
            ->all();
    }

    public function countAll(): int
    {
        return (int) CAT_Category::query()->count();
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
            $row->sort_order = ((int) CAT_Category::query()->max('sort_order')) + 1;
        }

        $row->fill([
            'name' => $name,
            'is_active' => $isActive,
            'is_accompanying' => $isAccompanying,
        ]);
        $row->save();

        return $this->mapper->toDomain($row);
    }

    public function reorder(array $orderedIds): void
    {
        if ($orderedIds === []) {
            return;
        }

        $existing = CAT_Category::query()
            ->whereIn('id', $orderedIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        $missing = array_diff($orderedIds, $existing);
        if ($missing !== []) {
            throw new \InvalidArgumentException('Неизвестные категории: '.implode(', ', $missing));
        }

        foreach ($orderedIds as $index => $categoryId) {
            CAT_Category::query()
                ->whereKey((int) $categoryId)
                ->update(['sort_order' => (int) $index]);
        }
    }

    public function replaceItems(int $categoryId, array $items): void
    {
        if (! CAT_Category::query()->whereKey($categoryId)->exists()) {
            throw new \InvalidArgumentException('Категория не найдена.');
        }

        $normalized = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }
            $itemId = (int) ($item['item_id'] ?? 0);
            if ($itemId < 1) {
                continue;
            }
            $sortOrder = isset($item['sort_order']) ? (int) $item['sort_order'] : $index;
            $normalized[$itemId] = $sortOrder;
        }

        if ($normalized !== []) {
            $existing = CAT_Item::query()
                ->whereIn('id', array_keys($normalized))
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->all();
            $missing = array_diff(array_keys($normalized), $existing);
            if ($missing !== []) {
                throw new \InvalidArgumentException('Неизвестные позиции каталога: '.implode(', ', $missing));
            }
        }

        CAT_CategoryItem::query()->where('category_id', $categoryId)->delete();

        foreach ($normalized as $itemId => $sortOrder) {
            CAT_CategoryItem::query()->create([
                'category_id' => $categoryId,
                'item_id' => $itemId,
                'sort_order' => $sortOrder,
            ]);
        }
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
