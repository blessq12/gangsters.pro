<?php

namespace App\Application\Catalog\Presenter;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductIngredient;
use App\Domain\Product\ReadModel\ProductAdminFormReadModel;
use App\Domain\Product\VO\ProductTag;
use App\Support\Money;

final class AdminProductPresenter
{
    public function presentListItem(Product $product): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'articul' => $product->articul(),
            'status' => $product->status(),
            'status_label' => $product->status() === Product::STATUS_ACTIVE ? 'Активен' : 'Архив',
            'price_rubles' => $product->price() !== null
                ? Money::kopecksToApiRubles($product->price())
                : null,
            'updated_at' => $product->updatedAt()->format(DATE_ATOM),
        ];
    }

    public function presentFormDetail(ProductAdminFormReadModel $readModel): array
    {
        $detail = $this->presentDetail($readModel->product, $readModel->slug);
        $detail['images_count'] = $readModel->imagesCount;

        return $detail;
    }

    public function presentDetail(Product $product, ?string $slug = null): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'slug' => $slug,
            'articul' => $product->articul(),
            'description' => $product->description(),
            'status' => $product->status(),
            'price_rubles' => $product->price() !== null
                ? Money::kopecksToApiRubles($product->price())
                : null,
            'nutrition' => [
                'calories' => $product->nutrition()->calories(),
                'proteins' => $product->nutrition()->proteins(),
                'fats' => $product->nutrition()->fats(),
                'carbs' => $product->nutrition()->carbs(),
                'basis' => $product->nutrition()->basis(),
            ],
            'ingredients' => array_map(
                static fn (ProductIngredient $row): array => [
                    'name' => $row->name(),
                    'amount' => $row->amount(),
                    'unit' => $row->unit(),
                    'is_allergen' => $row->isAllergen(),
                ],
                $product->ingredients(),
            ),
            'tags' => array_map(
                static fn (ProductTag $tag): array => [
                    'code' => $tag->code(),
                    'label' => $tag->label(),
                    'color' => $tag->color(),
                ],
                $product->tags(),
            ),
            'cart_rule_flags' => [
                'counts_as_roll' => $product->cartRuleCountsAsRoll(),
                'gift_candidate' => $product->cartRuleGiftCandidate(),
                'is_complement_set' => $product->cartRuleIsComplementSet(),
            ],
            'images_count' => count($product->images()),
        ];
    }
}
