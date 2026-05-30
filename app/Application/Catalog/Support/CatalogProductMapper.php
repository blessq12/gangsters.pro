<?php

namespace App\Application\Catalog\Support;

use App\Application\Catalog\DTO\AdminIngredientDTO;
use App\Application\Catalog\DTO\AdminNutritionDTO;
use App\Application\Catalog\DTO\CreateProductDTO;
use App\Application\Catalog\DTO\UpdateProductDTO;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductIngredient;
use App\Domain\Product\VO\Nutrition;
use App\Support\Money;

final class CatalogProductMapper
{
    public static function nutritionFromDto(AdminNutritionDTO $dto): Nutrition
    {
        return new Nutrition(
            calories: $dto->calories,
            proteins: $dto->proteins,
            fats: $dto->fats,
            carbs: $dto->carbs,
            basis: $dto->basis,
        );
    }

    /**
     * @param  AdminIngredientDTO[]  $rows
     * @return ProductIngredient[]
     */
    public static function ingredientsFromDtos(array $rows): array
    {
        return array_map(
            static fn (AdminIngredientDTO $row): ProductIngredient => ProductIngredient::create(
                name: $row->name,
                amount: $row->amount,
                unit: $row->unit,
                isAllergen: $row->isAllergen,
            ),
            $rows,
        );
    }

    public static function productFromCreateDto(CreateProductDTO $dto): Product
    {
        return Product::create(
            name: $dto->name,
            description: $dto->description,
            nutrition: self::nutritionFromDto($dto->nutrition),
            images: [],
            ingredients: self::ingredientsFromDtos($dto->ingredients),
            tags: [],
            price: Money::apiRublesToKopecks($dto->priceRubles),
            articul: $dto->articul,
        );
    }

    public static function applyUpdateDto(Product $product, UpdateProductDTO $dto): void
    {
        $product->rename($dto->name);
        $product->changeDescription($dto->description);
        $product->setArticul($dto->articul);
        $product->setNutrition(self::nutritionFromDto($dto->nutrition));
        $product->setIngredients(self::ingredientsFromDtos($dto->ingredients));
        $product->setPrice(Money::apiRublesToKopecks($dto->priceRubles));
    }
}
