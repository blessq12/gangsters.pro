<?php

namespace App\Application\YandexFood\Presenter;

use Carbon\Carbon;

/**
 * Формат ответа — как у прежнего легаси-эндпоинта composition (категории + items + lastChange).
 */
final class YandexFoodMenuCatalogPresenter
{
    /**
     * @param  list<array{
     *   category: array{id: string, name: string, sortOrder: int},
     *   lines: list<array{product: array{id: string, name: string, description: string, priceRubles: float}, sortOrder: int}>
     * }>  $blocks
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
                    (string) $block['category']['id'],
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
    private function presentCategory(array $category): array
    {
        return [
            'id' => (string) $category['id'],
            'name' => (string) $category['name'],
            'parentId' => null,
            'sortOrder' => (int) $category['sortOrder'],
            'images' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentMenuItem(array $product, string $categoryId, int $sortOrder): array
    {
        return [
            'id' => (string) ($product['id'] ?? ''),
            'categoryId' => $categoryId,
            'name' => (string) ($product['name'] ?? ''),
            'description' => (string) ($product['description'] ?? ''),
            'price' => (float) ($product['priceRubles'] ?? 0),
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
