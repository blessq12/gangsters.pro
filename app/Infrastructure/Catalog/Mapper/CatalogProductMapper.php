<?php

namespace App\Infrastructure\Catalog\Mapper;

use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Catalog\ValueObject\Nutrition;
use App\Domain\Supporting\Catalog\ValueObject\ProductImage;
use App\Infrastructure\Catalog\Model\CatalogItemModel;

final class CatalogProductMapper
{
    /**
     * @param  list<ProductImage>  $images
     */
    public function toDomain(CatalogItemModel $row, array $images = []): Product
    {
        return new Product(
            id: (int) $row->id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            sku: $this->resolveSku($row),
            status: $this->resolveStatus($row),
            isSystem: (bool) $row->is_system,
            isRoll: (bool) ($row->is_roll ?? false),
            description: $row->description !== null ? (string) $row->description : null,
            nutrition: $this->resolveNutrition($row),
            ingredients: $this->resolveIngredients($row),
            images: $images,
            weight: $this->resolveWeight($row),
        );
    }

    private function resolveWeight(CatalogItemModel $row): ?int
    {
        if ($row->weight === null) {
            return null;
        }

        $grams = (int) $row->weight;

        return $grams > 0 ? $grams : null;
    }

    private function resolveSku(CatalogItemModel $row): ?string
    {
        $sku = trim((string) ($row->sku ?? ''));

        return $sku !== '' ? $sku : null;
    }

    /**
     * @return list<string>
     */
    private function resolveIngredients(CatalogItemModel $row): array
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

    private function resolveStatus(CatalogItemModel $row): ProductStatus
    {
        if ($row->archived_at !== null) {
            return ProductStatus::Archived;
        }

        $status = strtolower((string) $row->status);

        return $status === ProductStatus::Active->value
            ? ProductStatus::Active
            : ProductStatus::Archived;
    }

    private function resolveNutrition(CatalogItemModel $row): ?Nutrition
    {
        $raw = $row->nutrition;
        if (! is_array($raw)) {
            return null;
        }

        $calories = (float) ($raw['calories'] ?? 0);
        $proteins = (float) ($raw['proteins'] ?? 0);
        $fats = (float) ($raw['fats'] ?? 0);
        $carbs = (float) ($raw['carbs'] ?? 0);

        if ($calories <= 0 && $proteins <= 0 && $fats <= 0 && $carbs <= 0) {
            return null;
        }

        return new Nutrition(
            calories: $calories,
            proteins: $proteins,
            fats: $fats,
            carbs: $carbs,
            basis: (string) ($raw['basis'] ?? 'per_100g'),
        );
    }
}
