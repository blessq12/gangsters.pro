<?php

namespace App\Application\YandexFood\Presenter;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;

final class YandexFoodMenuPresenter
{
    /**
     * @param  list<array{id: int, name: string, sort_order: int, has_items: bool}>  $categories
     * @param  list<array<string, mixed>>  $products
     * @return array{categories: list<array<string, mixed>>, items: list<array<string, mixed>>, lastChange: string}
     */
    public function presentComposition(
        array $categories,
        array $products,
        CarbonInterface $changedAt,
    ): array {
        $categoriesOutput = [];

        foreach ($categories as $category) {
            if (! $category['has_items']) {
                continue;
            }

            $categoriesOutput[] = [
                'id' => (string) $category['id'],
                'name' => $category['name'],
                'parentId' => null,
                'sortOrder' => $category['sort_order'],
                'images' => [],
            ];
        }

        $items = [];

        foreach ($products as $product) {
            $items[] = [
                'id' => (string) $product['id'],
                'categoryId' => (string) $product['category_id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => (float) $product['price_rubles'],
                'vat' => 0,
                'isCatchweight' => false,
                'measure' => 0,
                'weightQuantum' => null,
                'measureUnit' => 'г',
                'sortOrder' => $product['sort_order'],
                'modifierGroups' => [],
                'images' => $this->mapImages($product['image_paths']),
            ];
        }

        return [
            'categories' => $categoriesOutput,
            'items' => $items,
            'lastChange' => $changedAt->utc()->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    /**
     * @param  list<string>  $unavailableProductIds
     * @return array{items: list<array{id: string, quantity: int}>, modifiers: list<mixed>}
     */
    public function presentAvailability(array $unavailableProductIds): array
    {
        $items = [];

        foreach ($unavailableProductIds as $productId) {
            $items[] = [
                'id' => $productId,
                'quantity' => 0,
            ];
        }

        return [
            'items' => $items,
            'modifiers' => [],
        ];
    }

    /**
     * @return array{promoItems: list<mixed>}
     */
    public function presentPromos(): array
    {
        return [
            'promoItems' => [],
        ];
    }

    /**
     * @param  list<string>  $imagePaths
     * @return list<array{hash: string, url: string}>
     */
    private function mapImages(array $imagePaths): array
    {
        $mapped = [];

        foreach ($imagePaths as $path) {
            if ($path === '') {
                continue;
            }

            $url = $this->absoluteImageUrl($path);
            $mapped[] = [
                'hash' => $this->imageHash($path, $url),
                'url' => $url,
            ];
        }

        return $mapped;
    }

    private function absoluteImageUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        return url(Storage::disk('public')->url($normalized));
    }

    private function imageHash(string $path, string $absoluteUrl): string
    {
        $fullPath = storage_path('app/public/'.ltrim($path, '/'));

        if (is_file($fullPath)) {
            $hash = sha1_file($fullPath);

            if (is_string($hash) && $hash !== '') {
                return $hash;
            }
        }

        return hash('sha256', $absoluteUrl);
    }
}
