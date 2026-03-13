<?php

namespace App\Application\Product\Presenter;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductImage;
use App\Domain\Product\Entity\ProductIngredient;
use App\Domain\Product\VO\ImageVariant;
use App\Domain\Product\VO\Nutrition;
use App\Domain\Product\VO\Price;
use App\Domain\Product\VO\ProductTag;

final class ProductPresenter
{
    public function present(Product $product): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'description' => $product->description(),
            'status' => $product->status(),
            'nutrition' => $this->presentNutrition($product->nutrition()),
            'images' => array_map(
                fn (ProductImage $image) => $this->presentImage($image),
                $product->images(),
            ),
            'ingredients' => array_map(
                fn (ProductIngredient $ingredient) => $this->presentIngredient($ingredient),
                $product->ingredients(),
            ),
            'tags' => array_map(
                fn (ProductTag $tag) => $this->presentTag($tag),
                $product->tags(),
            ),
            'prices' => array_map(
                fn (Price $price) => $this->presentPrice($price),
                $product->prices(),
            ),
            'created_at' => $product->createdAt()->format(DATE_ATOM),
            'updated_at' => $product->updatedAt()->format(DATE_ATOM),
            'archived_at' => $product->archivedAt()?->format(DATE_ATOM),
        ];
    }

    private function presentNutrition(Nutrition $nutrition): array
    {
        return [
            'calories' => $nutrition->calories(),
            'proteins' => $nutrition->proteins(),
            'fats' => $nutrition->fats(),
            'carbs' => $nutrition->carbs(),
            'basis' => $nutrition->basis(),
        ];
    }

    private function presentImage(ProductImage $image): array
    {
        return [
            'id' => $image->id(),
            'sort_order' => $image->sortOrder(),
            'variants' => array_map(
                fn (ImageVariant $variant) => $this->presentVariant($variant),
                $image->variants(),
            ),
        ];
    }

    private function presentVariant(ImageVariant $variant): array
    {
        return [
            'size' => $variant->size(),
            'path' => $variant->path(),
            'width' => $variant->width(),
            'height' => $variant->height(),
        ];
    }

    private function presentIngredient(ProductIngredient $ingredient): array
    {
        return [
            'id' => $ingredient->id(),
            'name' => $ingredient->name(),
            'amount' => $ingredient->amount(),
            'unit' => $ingredient->unit(),
            'is_allergen' => $ingredient->isAllergen(),
        ];
    }

    private function presentTag(ProductTag $tag): array
    {
        return [
            'code' => $tag->code(),
        ];
    }

    private function presentPrice(Price $price): array
    {
        return [
            'amount' => $price->amount(),
            'customer_status' => $price->customerStatus()->code(),
            'is_default' => $price->isDefault(),
        ];
    }
}

