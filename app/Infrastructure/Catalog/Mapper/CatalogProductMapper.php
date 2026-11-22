<?php

namespace App\Infrastructure\Catalog\Mapper;

use App\Domain\Supporting\Catalog\Entity\Price;
use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Enum\PriceType;
use App\Domain\Supporting\Catalog\Enum\ProductStatus;
use App\Domain\Supporting\Catalog\ValueObject\Nutrition;
use App\Domain\Supporting\Catalog\ValueObject\ProductImage;
use App\Infrastructure\Catalog\Model\CAT_Item;
use App\Infrastructure\Catalog\Model\CAT_Price;

final class CatalogProductMapper
{
    /**
     * @param  list<int>  $tagIds
     * @param  list<ProductImage>  $images
     * @param  list<Price>  $prices
     */
    public function toDomain(CAT_Item $row, array $tagIds = [], array $images = [], array $prices = []): Product
    {
        return new Product(
            id: (int) $row->id,
            name: (string) $row->name,
            slug: (string) $row->slug,
            sku: $this->resolveSku($row),
            status: $this->resolveStatus($row),
            isSystem: (bool) $row->is_system,
            isRoll: (bool) ($row->is_roll ?? false),
            isComplement: (bool) ($row->is_complement ?? false),
            prices: $prices !== [] ? $prices : $this->mapPrices($row),
            description: $row->description !== null ? (string) $row->description : null,
            nutrition: $this->resolveNutrition($row),
            tagIds: $tagIds,
            ingredients: $this->resolveIngredients($row),
            images: $images,
            weight: $this->resolveWeight($row),
        );
    }

    /**
     * @return list<Price>
     */
    public function mapPrices(CAT_Item $row): array
    {
        $priceRows = $row->relationLoaded('prices')
            ? $row->prices
            : CAT_Price::query()->where('item_id', $row->id)->get();

        $prices = [];
        foreach ($priceRows as $priceRow) {
            if (! $priceRow instanceof CAT_Price) {
                continue;
            }

            $type = PriceType::tryFrom((string) $priceRow->type);
            if ($type === null) {
                continue;
            }

            $prices[] = Price::of(
                type: $type,
                amountKopecks: max(0, (int) $priceRow->amount_kopecks),
                id: (int) $priceRow->id,
            );
        }

        return $prices;
    }

    private function resolveWeight(CAT_Item $row): ?int
    {
        if ($row->weight === null) {
            return null;
        }

        $grams = (int) $row->weight;

        return $grams > 0 ? $grams : null;
    }

    private function resolveSku(CAT_Item $row): ?string
    {
        $sku = trim((string) ($row->sku ?? ''));

        return $sku !== '' ? $sku : null;
    }

    /**
     * @return list<string>
     */
    private function resolveIngredients(CAT_Item $row): array
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

    private function resolveStatus(CAT_Item $row): ProductStatus
    {
        if ($row->archived_at !== null) {
            return ProductStatus::Archived;
        }

        $status = strtolower((string) $row->status);

        return $status === ProductStatus::Active->value
            ? ProductStatus::Active
            : ProductStatus::Archived;
    }

    private function resolveNutrition(CAT_Item $row): ?Nutrition
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
