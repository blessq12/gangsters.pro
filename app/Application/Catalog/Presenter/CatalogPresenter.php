<?php

namespace App\Application\Catalog\Presenter;

use App\Domain\Catalog\Entity\Category;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Entity\Tag;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\ValueObject\Nutrition;
use App\Domain\Catalog\ValueObject\ProductImage;
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
            'price' => $this->money($product->price()),
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
            'price' => $this->money($set->price()),
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
            'price' => $product->price()->amountRubles(),
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
            'price' => $set->price()->amountRubles(),
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
    private function money(Money $money): array
    {
        return [
            'amount' => $money->amountRubles(),
            'currency' => $money->currency(),
        ];
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
