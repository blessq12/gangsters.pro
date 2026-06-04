<?php

namespace App\Filament\Support;

use App\Infrastructure\Category\Model\PRD_Category;
use App\Infrastructure\Category\Model\PRD_CategoryProduct;
use App\Infrastructure\Product\Model\PRD_Product;

final class AdminCatalogOverviewReadHelper
{
    /**
     * @return list<array{category: array<string, mixed>, products: list<array<string, mixed>>}>
     */
    public function categoryNodes(): array
    {
        $nodes = [];

        $categories = PRD_Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($categories as $category) {
            $links = PRD_CategoryProduct::query()
                ->where('category_id', $category->id)
                ->orderBy('sort_order')
                ->pluck('product_id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            $products = [];
            if ($links !== []) {
                $byId = PRD_Product::query()
                    ->whereIn('id', $links)
                    ->get(['id', 'name', 'status'])
                    ->keyBy('id');

                foreach ($links as $productId) {
                    $product = $byId->get($productId);
                    if ($product === null) {
                        continue;
                    }

                    $products[] = [
                        'id' => (int) $product->id,
                        'name' => (string) $product->name,
                        'status' => (string) $product->status,
                    ];
                }
            }

            $nodes[] = [
                'category' => [
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                    'slug' => (string) $category->slug,
                    'is_active' => (bool) $category->is_active,
                    'sort_order' => (int) $category->sort_order,
                ],
                'products' => $products,
            ];
        }

        return $nodes;
    }
}
