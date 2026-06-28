<?php

namespace App\Application\YandexFood\Presenter;

use App\Domain\Catalog\Entity\Category;
use App\Domain\Catalog\Entity\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;

final class YandexFoodMenuPresenter
{
    /**
     * @param  list<array{category: Category, has_items: bool}>  $categories
     * @param  list<array{partner_sku: string, category_id: int, product: Product, sort_order: int}>  $products
     * @return array{categories: list<array<string, mixed>>, items: list<array<string, mixed>>, lastChange: string}
     */
    public function presentComposition(
        array $categories,
        array $products,
        CarbonInterface $changedAt,
    ): array {
        $categoriesOutput = [];

        foreach ($categories as $row) {
            if (! $row['has_items']) {
                continue;
            }

            $category = $row['category'];
            $categoriesOutput[] = [
                'id' => (string) $category->id(),
                'name' => $category->name(),
                'parentId' => null,
                'sortOrder' => $category->sortOrder(),
                'images' => [],
            ];
        }

        $items = [];

        foreach ($products as $row) {
            $product = $row['product'];
            $items[] = [
                'id' => $row['partner_sku'],
                'categoryId' => (string) $row['category_id'],
                'name' => $product->name(),
                'description' => $product->description() ?? '',
                'price' => (float) $product->price()->amountRubles(),
                'vat' => 0,
                'isCatchweight' => false,
                'measure' => 0,
                'weightQuantum' => null,
                'measureUnit' => 'г',
                'sortOrder' => $row['sort_order'],
                'modifierGroups' => [],
                'images' => $this->mapImages($product),
            ];
        }

        return [
            'categories' => $categoriesOutput,
            'items' => $items,
            'lastChange' => $changedAt->utc()->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    /**
     * @param  list<string>  $unavailablePartnerSkus
     * @return array{items: list<array{id: string, quantity: int}>, modifiers: list<mixed>}
     */
    public function presentAvailability(array $unavailablePartnerSkus): array
    {
        $items = [];

        foreach ($unavailablePartnerSkus as $partnerSku) {
            $items[] = [
                'id' => $partnerSku,
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
     * @return list<array{hash: string, url: string}>
     */
    private function mapImages(Product $product): array
    {
        $mapped = [];

        foreach ($product->images() as $image) {
            $path = $image->path();
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
