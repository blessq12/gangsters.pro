<?php

namespace App\Application\Catalog\Presenter;

use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Entity\TechCard;
use App\Domain\Supporting\Catalog\ValueObject\Nutrition;
use App\Domain\Supporting\Catalog\ValueObject\ProductImage;
use App\Shared\ValueObject\Money;

/**
 * Единый формат ответа каталога для витрины и админки (товар без цен и тегов).
 */
final class CatalogPresenter
{
    /**
     * @param  list<array{code: string, label: string, color: string}>  $tags  теги приходят снаружи (из меню филиала)
     * @return array<string, mixed>
     */
    public function product(Product $product, array $tags = [], ?TechCard $techCard = null): array
    {
        return [
            'id' => $product->id(),
            'kind' => 'product',
            'name' => $product->name(),
            'slug' => $product->slug(),
            'sku' => $product->sku(),
            'status' => $product->status()->value,
            'is_system' => $product->isSystem(),
            'is_roll' => $product->isRoll(),
            'weight' => $product->weight(),
            'description' => $product->description(),
            'nutrition' => $this->nutrition($product->nutrition()),
            'ingredients' => $product->ingredients(),
            'tech_card_search' => $this->techCardSearchText($techCard),
            'tags' => $tags,
            'images' => $this->images($product->images()),
        ];
    }

    /**
     * Плоская строка для клиентского поиска по техкарте (имена ingredient_ref).
     */
    public function techCardSearchText(?TechCard $techCard): string
    {
        if (! $techCard instanceof TechCard || $techCard->isEmpty()) {
            return '';
        }

        $parts = [];
        foreach ($techCard->lines() as $line) {
            $ref = trim($line->ingredientRef());
            if ($ref !== '') {
                $parts[] = $ref;
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Подстановка продажной цены из меню филиала в payload товара.
     *
     * @param  array<string, mixed>  $item  результат product()
     * @return array<string, mixed>
     */
    public function withMenuPrice(array $item, int $priceKopecks): array
    {
        $item['price'] = ['amount' => intdiv($priceKopecks, 100), 'currency' => Money::CURRENCY];

        return $item;
    }

    /**
     * Упрощённый снимок товара для админ-списка.
     *
     * @return array<string, mixed>
     */
    public function adminProduct(Product $product): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'weight' => $product->weight(),
            'status' => $product->status()->value,
            'is_system' => $product->isSystem(),
            'is_roll' => $product->isRoll(),
            'description' => $product->description(),
            'sku' => $product->sku(),
        ];
    }

    /**
     * @param  list<array{id: int, url: string, path: string, sort_order: int, focus_x: float|null, focus_y: float|null}>  $images
     * @return array<string, mixed>
     */
    public function adminProductDetail(Product $product, array $images, ?TechCard $techCard = null): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'slug' => $product->slug(),
            'sku' => $product->sku(),
            'status' => $product->status()->value,
            'is_system' => $product->isSystem(),
            'is_roll' => $product->isRoll(),
            'description' => $product->description(),
            'weight' => $product->weight(),
            'ingredients' => $product->ingredients(),
            'nutrition' => $this->nutrition($product->nutrition()),
            'images' => $images,
            'tech_card' => $this->techCard($techCard),
        ];
    }

    /**
     * @return array{lines: list<array{ingredient_ref: string, quantity: float, unit: string}>}
     */
    private function techCard(?TechCard $techCard): array
    {
        if (! $techCard instanceof TechCard) {
            return ['lines' => []];
        }

        $lines = [];
        foreach ($techCard->lines() as $line) {
            $lines[] = [
                'ingredient_ref' => $line->ingredientRef(),
                'quantity' => $line->quantity(),
                'unit' => $line->unit(),
            ];
        }

        return ['lines' => $lines];
    }

    /**
     * @param  list<ProductImage>  $images
     * @return list<array{variants: list<array{size: string, path: string, width: int}>, focus: array{x: float, y: float}}>
     */
    private function images(array $images): array
    {
        $mapped = [];
        foreach ($images as $image) {
            if (! $image instanceof ProductImage) {
                continue;
            }

            $path = $image->path();
            if ($path === '') {
                continue;
            }

            $mapped[] = [
                'variants' => [
                    ['size' => 'thumb', 'path' => $path, 'width' => 300],
                    ['size' => 'medium', 'path' => $path, 'width' => 800],
                    ['size' => 'large', 'path' => $path, 'width' => 1200],
                ],
                'focus' => $image->focusPayload(),
            ];
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function nutrition(?Nutrition $nutrition): ?array
    {
        if (! $nutrition instanceof Nutrition) {
            return null;
        }

        return [
            'calories' => $nutrition->calories(),
            'proteins' => $nutrition->proteins(),
            'fats' => $nutrition->fats(),
            'carbs' => $nutrition->carbs(),
            'basis' => $nutrition->basis(),
        ];
    }
}
