<?php

namespace App\Filament\Support;

use App\Application\Common\Exceptions\ApiException;
use App\Infrastructure\Category\Model\PRD_Category;
use App\Infrastructure\Category\Model\PRD_CategoryProduct;
use App\Infrastructure\Product\Model\PRD_Product;
final class AdminCatalogLayoutReadHelper
{
    /**
     * @return array<int, string>
     */
    public function categoryOptions(): array
    {
        return PRD_Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn (PRD_Category $category): array => [
                (int) $category->id => (string) $category->name,
            ])
            ->all();
    }

    public function categoryExists(int $categoryId): bool
    {
        return PRD_Category::query()->whereKey($categoryId)->exists();
    }

    /**
     * @return array{category_id: int, category_name: string, products: list<array<string, mixed>>}
     */
    public function layoutForCategory(int $categoryId): array
    {
        $category = PRD_Category::query()->find($categoryId);
        if ($category === null) {
            throw new ApiException('Category not found.', 404);
        }

        $links = PRD_CategoryProduct::query()
            ->where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->get();

        $productIds = $links->pluck('product_id')->map(static fn ($id): int => (int) $id)->all();
        if ($productIds === []) {
            return [
                'category_id' => $categoryId,
                'category_name' => (string) $category->name,
                'products' => [],
            ];
        }

        $productsById = PRD_Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($productIds as $id) {
            $product = $productsById->get($id);
            if ($product === null) {
                continue;
            }

            $items[] = app(AdminProductTableQuery::class)->presentListItem($product);
        }

        return [
            'category_id' => $categoryId,
            'category_name' => (string) $category->name,
            'products' => $items,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function productOptionsForSelect(int $limit = 200): array
    {
        return PRD_Product::query()
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name'])
            ->mapWithKeys(fn (PRD_Product $product): array => [
                (int) $product->id => (string) $product->name,
            ])
            ->all();
    }
}
