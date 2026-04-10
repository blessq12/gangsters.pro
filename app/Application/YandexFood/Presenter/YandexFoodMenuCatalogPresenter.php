<?php

namespace App\Application\YandexFood\Presenter;

use App\Domain\Category\Entity\Category;
use App\Domain\Product\Entity\Product;
use App\Support\Money;
use Carbon\Carbon;

/**
 * Формат ответа — как у прежнего легаси-эндпоинта composition (категории + items + lastChange).
 */
final class YandexFoodMenuCatalogPresenter
{
    /**
     * @param  list<array{category: Category, lines: list<array{product: Product, sortOrder: int}>}>  $blocks
     * @return array{categories: list<array<string, mixed>>, items: list<array<string, mixed>>, lastChange: string}
     */
    public function presentMenuComposition(array $blocks): array
    {
        $categories = [];
        $items = [];

        foreach ($blocks as $block) {
            $categories[] = $this->presentCategory($block['category']);
            foreach ($block['lines'] as $line) {
                $items[] = $this->presentMenuItem(
                    $line['product'],
                    (string) $block['category']->id(),
                    $line['sortOrder'],
                );
            }
        }

        return [
            'categories' => $categories,
            'items' => $items,
            'lastChange' => Carbon::now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    /**
     * @return array{id: string, name: string, parentId: null, sortOrder: int, images: array<int, mixed>}
     */
    private function presentCategory(Category $category): array
    {
        return [
            'id' => (string) $category->id(),
            'name' => $category->name(),
            'parentId' => null,
            'sortOrder' => $category->sortOrder(),
            'images' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentMenuItem(Product $product, string $categoryId, int $sortOrder): array
    {
        $priceRub = $product->price() !== null
            ? Money::kopecksToApiRubles($product->price())
            : 0.0;

        return [
            'id' => (string) $product->id(),
            'categoryId' => $categoryId,
            'name' => $product->name(),
            'description' => $product->description(),
            'price' => $priceRub,
            'vat' => 0,
            'isCatchweight' => false,
            'measure' => 0,
            'weightQuantum' => null,
            'measureUnit' => 'г',
            'sortOrder' => $sortOrder,
            'modifierGroups' => [],
        ];
    }
}
