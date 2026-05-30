<?php

namespace Tests\Unit\Filament\Catalog;

use App\Filament\Catalog\Support\FilamentProductFormMapper;
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
    public function to_form_state_maps_detail_payload(): void
    {
        $state = FilamentProductFormMapper::toFormState([
            'name' => 'Товар',
            'articul' => 'X',
            'description' => 'D',
            'price_rubles' => 10.0,
            'status' => 'active',
            'slug' => 'tovar',
            'nutrition' => ['calories' => 1, 'proteins' => 2, 'fats' => 3, 'carbs' => 4, 'basis' => 'per_100g'],
            'ingredients' => [],
            'tags' => [['code' => 'spicy', 'label' => 'Острое']],
            'cart_rule_flags' => [
                'counts_as_roll' => true,
                'gift_candidate' => false,
                'is_complement_set' => true,
            ],
            'images_count' => 2,
        ]);

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
        $ingredients = array_fill(0, 150, [
            'name' => 'Item',
            'amount' => '1',
            'unit' => 'g',
            'is_allergen' => false,
        ]);

        $state = FilamentProductFormMapper::toFormState([
            'name' => 'Товар',
            'description' => '',
            'status' => 'active',
            'ingredients' => $ingredients,
        ]);

        $this->assertCount(FilamentProductFormMapper::FORM_INGREDIENT_LIMIT, $state['ingredients']);
    }
}
