<?php

namespace App\Infrastructure\Catalog\Mapper;

use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Domain\Catalog\ValueObject\Nutrition;
use App\Domain\Catalog\ValueObject\ProductImage;
use App\Infrastructure\Catalog\Model\PRD_Product;
use App\Shared\ValueObject\Money;

final class CatalogProductMapper
{
    /**
     * @param  list<int>  $tagIds
     * @param  list<ProductImage>  $images
     */
    public function toDomain(PRD_Product $row, array $tagIds = [], array $images = []): Product
    {
        return new Product(
            id: (int) $row->id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            sku: $this->resolveSku($row),
            status: $this->resolveStatus($row),
            isSystem: (bool) $row->is_system,
            price: Money::rubles((int) ($row->price ?? 0)),
            description: $row->description !== null ? (string) $row->description : null,
            nutrition: $this->resolveNutrition($row),
            tagIds: $tagIds,
            ingredients: $this->resolveIngredients($row),
            images: $images,
        );
    }

    private function resolveSku(PRD_Product $row): ?string
    {
        $sku = trim((string) ($row->sku ?? ''));

        return $sku !== '' ? $sku : null;
    }

    private function resolveIngredients(PRD_Product $row): array
    {
        $raw = $row->ingredients;

        if (! is_array($raw)) {
            return [];
        }

        $ingredients = [];

        foreach ($raw as $value) {
            $name = trim((string) $value);

            if ($name === '') {
                continue;
            }

            $ingredients[] = $name;
        }

        return $ingredients;
    }

    private function resolveStatus(PRD_Product $row): ProductStatus
    {
        if ($row->archived_at !== null) {
            return ProductStatus::Archived;
        }

        $status = strtolower((string) $row->status);

        return $status === ProductStatus::Active->value
            ? ProductStatus::Active
            : ProductStatus::Archived;
    }

    private function resolveNutrition(PRD_Product $row): ?Nutrition
    {
        $hasValues = ((float) $row->calories) > 0
            || ((float) $row->proteins) > 0
            || ((float) $row->fats) > 0
            || ((float) $row->carbs) > 0;

        if (! $hasValues) {
            return null;
        }

        return new Nutrition(
            calories: (float) $row->calories,
            proteins: (float) $row->proteins,
            fats: (float) $row->fats,
            carbs: (float) $row->carbs,
            basis: (string) ($row->nutrition_basis ?: 'per_100g'),
        );
    }
}
