<?php

namespace Tests\Unit\Filament\Catalog;

use App\Domain\Product\Entity\Product;
use App\Filament\Catalog\Support\FilamentProductFormMapper;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Product\Model\PRD_Tag;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FilamentProductFormMapperTest extends TestCase
{
    #[Test]
    public function to_create_dto_maps_core_fields(): void
    {
        $dto = FilamentProductFormMapper::toCreateDto([
            'name' => 'Ролл',
            'description' => 'Описание',
            'price_rubles' => 350.5,
            'articul' => 'A-1',
            'nutrition' => [
                'calories' => 100,
                'proteins' => 5,
                'fats' => 2,
                'carbs' => 10,
                'basis' => 'per_100g',
            ],
            'ingredients' => [
                ['name' => 'Рис', 'amount' => '100', 'unit' => 'г', 'is_allergen' => false],
                ['name' => '', 'amount' => '', 'unit' => '', 'is_allergen' => false],
            ],
        ]);

        $this->assertSame('Ролл', $dto->name);
        $this->assertSame('Описание', $dto->description);
        $this->assertSame(350.5, $dto->priceRubles);
        $this->assertSame('A-1', $dto->articul);
        $this->assertCount(1, $dto->ingredients);
        $this->assertSame('Рис', $dto->ingredients[0]->name);
    }

    #[Test]
    public function to_form_state_maps_product_model(): void
    {
        $product = new PRD_Product([
            'name' => 'Товар',
            'articul' => 'X',
            'description' => 'D',
            'price' => 1000,
            'status' => Product::STATUS_ACTIVE,
            'slug' => 'tovar',
            'calories' => 1,
            'proteins' => 2,
            'fats' => 3,
            'carbs' => 4,
            'nutrition_basis' => 'per_100g',
            'cart_rule_counts_as_roll' => true,
            'cart_rule_gift_candidate' => false,
            'cart_rule_is_complement_set' => true,
        ]);
        $product->setRelation('tags', new Collection([
            new PRD_Tag(['code' => 'spicy', 'label' => 'Острое']),
        ]));
        $product->setRelation('ingredients', new Collection());
        $product->setRelation('images', new Collection([1, 2]));

        $state = FilamentProductFormMapper::toFormState($product);

        $this->assertSame('Товар', $state['name']);
        $this->assertSame(['spicy'], $state['tag_codes']);
        $this->assertTrue($state['counts_as_roll']);
        $this->assertTrue($state['is_complement_set']);
        $this->assertSame(2, $state['images_count']);
        $this->assertSame('Активен', $state['status_label']);
    }

    #[Test]
    public function to_form_state_truncates_ingredients_for_admin_form(): void
    {
        $ingredients = new Collection();
        for ($i = 0; $i < 150; $i++) {
            $ingredients->push(new \App\Infrastructure\Product\Model\PRD_ProductIngredient([
                'name' => 'Item',
                'amount' => '1',
                'unit' => 'g',
                'is_allergen' => false,
            ]));
        }

        $product = new PRD_Product([
            'name' => 'Товар',
            'description' => '',
            'status' => Product::STATUS_ACTIVE,
        ]);
        $product->setRelation('tags', new Collection());
        $product->setRelation('ingredients', $ingredients);
        $product->setRelation('images', new Collection());

        $state = FilamentProductFormMapper::toFormState($product);

        $this->assertCount(FilamentProductFormMapper::FORM_INGREDIENT_LIMIT, $state['ingredients']);
    }
}
