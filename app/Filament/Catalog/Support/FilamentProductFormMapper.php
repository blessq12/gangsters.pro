<?php

namespace App\Filament\Catalog\Support;

use App\Application\Catalog\DTO\AdminIngredientDTO;
use App\Application\Catalog\DTO\AdminNutritionDTO;
use App\Application\Catalog\DTO\CreateProductDTO;
use App\Application\Catalog\DTO\SyncProductTagsDTO;
use App\Application\Catalog\DTO\UpdateCartRuleFlagsDTO;
use App\Application\Catalog\DTO\UpdateProductDTO;
use App\Domain\Product\Entity\Product;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Product\Model\PRD_ProductIngredient;
use App\Support\Money;
use App\Support\Product\ProductStatusLabels;

final class FilamentProductFormMapper
{
    public const FORM_INGREDIENT_LIMIT = 100;

    public static function toFormState(PRD_Product $product): array
    {
        $product->loadMissing(['tags', 'ingredients', 'images']);
        $status = (string) $product->status;
        $ingredients = $product->ingredients
            ->map(static fn (PRD_ProductIngredient $row): array => [
                'name' => (string) $row->name,
                'amount' => $row->amount,
                'unit' => $row->unit,
                'is_allergen' => (bool) $row->is_allergen,
            ])
            ->values()
            ->all();

        if (count($ingredients) > self::FORM_INGREDIENT_LIMIT) {
            $ingredients = array_slice($ingredients, 0, self::FORM_INGREDIENT_LIMIT);
        }

        return [
            'name' => (string) $product->name,
            'articul' => $product->articul,
            'description' => (string) ($product->description ?? ''),
            'price_rubles' => $product->price !== null
                ? Money::kopecksToApiRubles((int) $product->price)
                : null,
            'status' => $status,
            'status_label' => ProductStatusLabels::label($status),
            'slug' => $product->slug,
            'nutrition' => [
                'calories' => (float) ($product->calories ?? 0),
                'proteins' => (float) ($product->proteins ?? 0),
                'fats' => (float) ($product->fats ?? 0),
                'carbs' => (float) ($product->carbs ?? 0),
                'basis' => (string) ($product->nutrition_basis ?? 'per_100g'),
            ],
            'ingredients' => $ingredients,
            'tag_codes' => $product->tags->pluck('code')->map(static fn ($code): string => (string) $code)->all(),
            'counts_as_roll' => (bool) $product->cart_rule_counts_as_roll,
            'gift_candidate' => (bool) $product->cart_rule_gift_candidate,
            'is_complement_set' => (bool) $product->cart_rule_is_complement_set,
            'images_count' => $product->images->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function emptyFormState(): array
    {
        return [
            'name' => '',
            'articul' => null,
            'description' => '',
            'price_rubles' => null,
            'status' => Product::STATUS_ACTIVE,
            'slug' => null,
            'nutrition' => self::emptyNutrition(),
            'ingredients' => [],
            'tag_codes' => [],
            'counts_as_roll' => false,
            'gift_candidate' => false,
            'is_complement_set' => false,
            'images_count' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toCreateDto(array $data): CreateProductDTO
    {
        return new CreateProductDTO(
            name: (string) ($data['name'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            nutrition: AdminNutritionDTO::fromArray((array) ($data['nutrition'] ?? [])),
            ingredients: self::mapIngredients($data),
            priceRubles: $data['price_rubles'] ?? null,
            articul: $data['articul'] ?? null,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toUpdateDto(int $productId, array $data): UpdateProductDTO
    {
        $create = self::toCreateDto($data);

        return new UpdateProductDTO(
            productId: $productId,
            name: $create->name,
            description: $create->description,
            nutrition: $create->nutrition,
            ingredients: $create->ingredients,
            priceRubles: $create->priceRubles,
            articul: $create->articul,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toCartRuleFlagsDto(int $productId, array $data): UpdateCartRuleFlagsDTO
    {
        return new UpdateCartRuleFlagsDTO(
            productId: $productId,
            countsAsRoll: (bool) ($data['counts_as_roll'] ?? false),
            giftCandidate: (bool) ($data['gift_candidate'] ?? false),
            isComplementSet: (bool) ($data['is_complement_set'] ?? false),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toSyncTagsDto(int $productId, array $data): SyncProductTagsDTO
    {
        $codes = $data['tag_codes'] ?? [];

        return new SyncProductTagsDTO(
            productId: $productId,
            tagCodes: is_array($codes) ? array_values(array_map('strval', $codes)) : [],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return AdminIngredientDTO[]
     */
    private static function mapIngredients(array $data): array
    {
        $ingredients = [];

        foreach ((array) ($data['ingredients'] ?? []) as $row) {
            if (! is_array($row) || ($row['name'] ?? '') === '') {
                continue;
            }

            $ingredients[] = AdminIngredientDTO::fromArray($row);
        }

        return $ingredients;
    }

    /**
     * @return array<string, mixed>
     */
    private static function emptyNutrition(): array
    {
        return [
            'calories' => 0,
            'proteins' => 0,
            'fats' => 0,
            'carbs' => 0,
            'basis' => 'per_100g',
        ];
    }
}
