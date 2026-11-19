<?php

namespace App\Application\Catalog\Presenter;

use App\Domain\Supporting\Catalog\Entity\Category;
use App\Domain\Supporting\Catalog\Entity\Price;
use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Entity\ProductSet;
use App\Domain\Supporting\Catalog\Entity\Tag;
use App\Domain\Supporting\Catalog\Enum\CatalogItemKind;
use App\Domain\Supporting\Catalog\Enum\PriceType;
use App\Domain\Supporting\Catalog\ValueObject\Nutrition;
use App\Domain\Supporting\Catalog\ValueObject\ProductImage;
use App\Shared\ValueObject\Money;

/**
 * Единый формат ответа каталога для витрины и админки.
 */
final class CatalogPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function category(Category $category): array
    {
        return [
            'id' => $category->id(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'sort_order' => $category->sortOrder(),
            'is_active' => $category->isActive(),
            'is_accompanying' => $category->isAccompanying(),
        ];
    }

    /**
     * @param  array<int, Tag>  $tagById
     * @return array<string, mixed>
     */
    public function product(Product $product, array $tagById): array
    {
        return [
            'kind' => CatalogItemKind::Product->value,
            'id' => $product->id(),
            'name' => $product->name(),
            'slug' => $product->slug(),
            'sku' => $product->sku(),
            'status' => $product->status()->value,
            'is_system' => $product->isSystem(),
            'price' => $this->money($product->regularPrice()),
            'old_price' => $this->optionalMoney($product->price(PriceType::Old)),
            'prices' => $this->prices($product->prices()),
            'weight' => $product->weight(),
            'description' => $product->description(),
            'nutrition' => $this->nutrition($product->nutrition()),
            'ingredients' => $product->ingredients(),
            'tags' => $this->tags($product->tagIds(), $tagById),
            'images' => $this->images($product->images()),
        ];
    }

    /**
     * @param  array<int, Tag>  $tagById
     * @param  array<int, string>  $lineProductNames
     * @param  array<int, Product>  $lineProductsById
     * @return array<string, mixed>
     */
    public function productSet(
        ProductSet $set,
        array $tagById,
        array $lineProductNames,
        array $lineProductsById,
    ): array {
        $lines = [];
        foreach ($set->lines() as $line) {
            $productId = $line->productId();
            $lines[] = [
                'product_id' => $productId,
                'quantity' => $line->quantity(),
                'product_name' => $lineProductNames[$productId] ?? null,
            ];
        }

        return [
            'kind' => CatalogItemKind::Set->value,
            'id' => $set->id(),
            'name' => $set->name(),
            'slug' => $set->slug(),
            'sku' => $set->sku(),
            'status' => $set->status()->value,
            'price' => $this->money($set->regularPrice()),
            'old_price' => $this->optionalMoney($set->price(PriceType::Old)),
            'prices' => $this->prices($set->prices()),
            'weight' => $this->setWeightGrams($set, $lineProductsById),
            'description' => $set->description(),
            'lines' => $lines,
            'tags' => $this->tags($set->tagIds(), $tagById),
            'images' => $this->images($set->images()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function tag(Tag $tag): array
    {
        return [
            'id' => $tag->id(),
            'code' => $tag->code(),
            'label' => $tag->label(),
            'color' => $tag->color(),
            'is_active' => $tag->isActive(),
            'sort_order' => $tag->sortOrder(),
        ];
    }

    /**
     * Упрощённый снимок товара для админ-списка.
     *
     * @return array<string, mixed>
     */
    public function adminProduct(Product $product, ?int $categoryId): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'category_id' => $categoryId,
            'price' => $product->regularPrice()?->amountRubles() ?? 0,
            'weight' => $product->weight(),
            'status' => $product->status()->value,
            'is_system' => $product->isSystem(),
            'description' => $product->description(),
            'sku' => $product->sku(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function adminSet(ProductSet $set): array
    {
        return [
            'id' => $set->id(),
            'name' => $set->name(),
            'slug' => $set->slug(),
            'sku' => $set->sku(),
            'line_count' => count($set->lines()),
            'price' => $set->regularPrice()?->amountRubles() ?? 0,
            'status' => $set->status()->value,
        ];
    }

    /**
     * @param  list<int>  $tagIds
     * @param  array<int, Tag>  $tagById
     * @return list<array{code: string, label: string, color: string}>
     */
    private function tags(array $tagIds, array $tagById): array
    {
        $tags = [];
        foreach ($tagIds as $tagId) {
            $tag = $tagById[$tagId] ?? null;
            if (! $tag instanceof Tag) {
                continue;
            }

            $tags[] = [
                'code' => $tag->code(),
                'label' => $tag->label(),
                'color' => $tag->color(),
            ];
        }

        return $tags;
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
     * @return array{amount: int, currency: string}
     */
    private function money(?Price $price): array
    {
        return [
            'amount' => $price?->amountRubles() ?? 0,
            'currency' => Money::CURRENCY,
        ];
    }

    /**
     * @return array{amount: int, currency: string}|null
     */
    private function optionalMoney(?Price $price): ?array
    {
        if (! $price instanceof Price || $price->amountKopecks() <= 0) {
            return null;
        }

        return $this->money($price);
    }

    /**
     * @param  list<Price>  $prices
     * @return list<array{type: string, amount_kopecks: int, amount_rubles: int}>
     */
    private function prices(array $prices): array
    {
        $mapped = [];
        foreach ($prices as $price) {
            if (! $price instanceof Price) {
                continue;
            }

            $mapped[] = [
                'type' => $price->type()->value,
                'amount_kopecks' => $price->amountKopecks(),
                'amount_rubles' => $price->amountRubles(),
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

    /**
     * @param  array<int, Product>  $lineProductsById
     */
    private function setWeightGrams(ProductSet $set, array $lineProductsById): ?int
    {
        $total = 0;
        foreach ($set->lines() as $line) {
            $product = $lineProductsById[$line->productId()] ?? null;
            if (! $product instanceof Product) {
                return null;
            }

            $grams = $product->weight();
            if ($grams === null || $grams <= 0) {
                return null;
            }

            $total += $grams * $line->quantity();
        }

        return $total > 0 ? $total : null;
    }
}
