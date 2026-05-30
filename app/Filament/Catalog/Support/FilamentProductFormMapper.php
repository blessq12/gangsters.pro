<?php

namespace App\Filament\Catalog\Support;

use App\Application\Catalog\DTO\AdminIngredientDTO;
use App\Application\Catalog\DTO\AdminNutritionDTO;
use App\Application\Catalog\DTO\CreateProductDTO;
use App\Application\Catalog\DTO\SyncProductTagsDTO;
use App\Application\Catalog\DTO\UpdateCartRuleFlagsDTO;
use App\Application\Catalog\DTO\UpdateProductDTO;
use App\Domain\Product\Entity\Product;
use App\Support\Product\ProductStatusLabels;

final class FilamentProductFormMapper
{
    public const FORM_INGREDIENT_LIMIT = 100;

    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        $flags = $detail['cart_rule_flags'] ?? [];
        $status = $detail['status'] ?? Product::STATUS_ACTIVE;
        $ingredients = $detail['ingredients'] ?? [];

        if (count($ingredients) > self::FORM_INGREDIENT_LIMIT) {
            $ingredients = array_slice($ingredients, 0, self::FORM_INGREDIENT_LIMIT);
        }

        return [
            'name' => $detail['name'] ?? '',
            'articul' => $detail['articul'] ?? null,
            'description' => $detail['description'] ?? '',
            'price_rubles' => $detail['price_rubles'] ?? null,
            'status' => $status,
            'status_label' => ProductStatusLabels::label((string) $status),
            'slug' => $detail['slug'] ?? null,
            'nutrition' => $detail['nutrition'] ?? self::emptyNutrition(),
            'ingredients' => $ingredients,
            'tag_codes' => array_map(
                static fn (array $tag): string => (string) $tag['code'],
                $detail['tags'] ?? [],
            ),
            'counts_as_roll' => (bool) ($flags['counts_as_roll'] ?? false),
            'gift_candidate' => (bool) ($flags['gift_candidate'] ?? false),
            'is_complement_set' => (bool) ($flags['is_complement_set'] ?? false),
            'images_count' => (int) ($detail['images_count'] ?? 0),
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
