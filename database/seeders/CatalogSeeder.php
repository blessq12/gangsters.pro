<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Infrastructure\Catalog\Model\PRD_Category;
use App\Infrastructure\Catalog\Model\PRD_CategoryProduct;
use App\Infrastructure\Catalog\Model\PRD_Product;
use App\Infrastructure\Catalog\Model\PRD_ProductSetLine;
use App\Infrastructure\Catalog\Model\PRD_Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $tags = $this->seedTags();
            $categories = $this->seedCategories();
            $products = $this->seedProducts();
            $sets = $this->seedSets($products);

            $this->seedCategoryAssignments($categories, $products, $sets);
            $this->seedProductTags($tags, $products, $sets);
        });
    }

    /**
     * @return array<string, PRD_Tag>
     */
    private function seedTags(): array
    {
        $definitions = [
            'new' => ['label' => 'Новинка', 'color' => 'emerald', 'sort_order' => 0],
            'hit' => ['label' => 'Хит', 'color' => 'amber', 'sort_order' => 1],
            'spicy' => ['label' => 'Острое', 'color' => 'red', 'sort_order' => 2],
            'vegan' => ['label' => 'Веган', 'color' => 'green', 'sort_order' => 3],
        ];

        $tags = [];

        foreach ($definitions as $code => $attributes) {
            $tags[$code] = PRD_Tag::query()->updateOrCreate(
                ['code' => $code],
                [
                    'label' => $attributes['label'],
                    'color' => $attributes['color'],
                    'is_active' => true,
                    'sort_order' => $attributes['sort_order'],
                ],
            );
        }

        return $tags;
    }

    /**
     * @return array<string, PRD_Category>
     */
    private function seedCategories(): array
    {
        $definitions = [
            'rolly' => ['name' => 'Роллы', 'sort_order' => 0],
            'sushi' => ['name' => 'Суши', 'sort_order' => 1],
            'napitki' => ['name' => 'Напитки', 'sort_order' => 2],
            'nabory' => ['name' => 'Наборы', 'sort_order' => 3],
        ];

        $categories = [];

        foreach ($definitions as $slug => $attributes) {
            $categories[$slug] = PRD_Category::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $attributes['name'],
                    'sort_order' => $attributes['sort_order'],
                    'is_active' => true,
                ],
            );
        }

        return $categories;
    }

    /**
     * @return array<string, PRD_Product>
     */
    private function seedProducts(): array
    {
        $definitions = [
            'filadelfiya' => [
                'name' => 'Филадельфия',
                'description' => 'Классический ролл с лососем, сливочным сыром и огурцом.',
                'price' => 450,
                'calories' => 142,
                'proteins' => 6.2,
                'fats' => 5.8,
                'carbs' => 17.4,
                'ingredients' => ['рис', 'лосось', 'сыр сливочный', 'огурец', 'нори'],
                'meta_counts_as_roll' => true,
            ],
            'kaliforniya' => [
                'name' => 'Калифорния',
                'description' => 'Ролл с крабом, авокадо и икрой тобико.',
                'price' => 390,
                'calories' => 128,
                'proteins' => 5.1,
                'fats' => 4.2,
                'carbs' => 18.1,
                'ingredients' => ['рис', 'краб', 'авокадо', 'тобико', 'нори'],
                'meta_counts_as_roll' => true,
            ],
            'kappa-maki' => [
                'name' => 'Каппа маки',
                'description' => 'Лёгкий вегетарианский ролл с огурцом.',
                'price' => 180,
                'calories' => 96,
                'proteins' => 2.1,
                'fats' => 0.4,
                'carbs' => 20.5,
                'ingredients' => ['рис', 'огурец', 'нори'],
                'meta_counts_as_roll' => true,
                'meta_gift_candidate' => true,
            ],
            'zeleny-chay' => [
                'name' => 'Зелёный чай',
                'description' => 'Горячий зелёный чай, 300 мл.',
                'price' => 120,
                'calories' => 1,
                'proteins' => 0,
                'fats' => 0,
                'carbs' => 0.2,
                'ingredients' => ['вода', 'зелёный чай'],
                'meta_counts_as_roll' => false,
            ],
        ];

        $products = [];

        foreach ($definitions as $slug => $attributes) {
            $products[$slug] = PRD_Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $attributes['name'],
                    'description' => $attributes['description'],
                    'status' => ProductStatus::Active->value,
                    'catalog_kind' => CatalogItemKind::Product->value,
                    'price' => $attributes['price'],
                    'calories' => $attributes['calories'],
                    'proteins' => $attributes['proteins'],
                    'fats' => $attributes['fats'],
                    'carbs' => $attributes['carbs'],
                    'nutrition_basis' => 'per_100g',
                    'ingredients' => $attributes['ingredients'],
                    'meta_counts_as_roll' => $attributes['meta_counts_as_roll'],
                    'meta_gift_candidate' => $attributes['meta_gift_candidate'] ?? false,
                    'meta_is_complement_set' => false,
                    'archived_at' => null,
                ],
            );
        }

        return $products;
    }

    /**
     * @param  array<string, PRD_Product>  $products
     * @return array<string, PRD_Product>
     */
    private function seedSets(array $products): array
    {
        $set = PRD_Product::query()->updateOrCreate(
            ['slug' => 'nabor-dva-rolla'],
            [
                'name' => 'Набор «2 ролла»',
                'description' => 'Филадельфия и Калифорния в одном наборе.',
                'status' => ProductStatus::Active->value,
                'catalog_kind' => CatalogItemKind::Set->value,
                'price' => 790,
                'calories' => 0,
                'proteins' => 0,
                'fats' => 0,
                'carbs' => 0,
                'nutrition_basis' => 'per_100g',
                'ingredients' => null,
                'meta_counts_as_roll' => false,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => false,
                'archived_at' => null,
            ],
        );

        $lines = [
            ['product' => 'filadelfiya', 'quantity' => 1, 'sort_order' => 0],
            ['product' => 'kaliforniya', 'quantity' => 1, 'sort_order' => 1],
        ];

        foreach ($lines as $line) {
            PRD_ProductSetLine::query()->updateOrCreate(
                [
                    'set_id' => $set->id,
                    'product_id' => $products[$line['product']]->id,
                ],
                [
                    'quantity' => $line['quantity'],
                    'sort_order' => $line['sort_order'],
                ],
            );
        }

        return ['nabor-dva-rolla' => $set];
    }

    /**
     * @param  array<string, PRD_Category>  $categories
     * @param  array<string, PRD_Product>  $products
     * @param  array<string, PRD_Product>  $sets
     */
    private function seedCategoryAssignments(array $categories, array $products, array $sets): void
    {
        $assignments = [
            ['category' => 'rolly', 'item' => 'filadelfiya', 'sort_order' => 0],
            ['category' => 'rolly', 'item' => 'kaliforniya', 'sort_order' => 1],
            ['category' => 'sushi', 'item' => 'kappa-maki', 'sort_order' => 0],
            ['category' => 'napitki', 'item' => 'zeleny-chay', 'sort_order' => 0],
            ['category' => 'nabory', 'item' => 'nabor-dva-rolla', 'sort_order' => 0],
        ];

        foreach ($assignments as $assignment) {
            $product = $products[$assignment['item']] ?? $sets[$assignment['item']] ?? null;

            if ($product === null) {
                continue;
            }

            PRD_CategoryProduct::query()->updateOrCreate(
                [
                    'category_id' => $categories[$assignment['category']]->id,
                    'product_id' => $product->id,
                ],
                [
                    'sort_order' => $assignment['sort_order'],
                ],
            );
        }
    }

    /**
     * @param  array<string, PRD_Tag>  $tags
     * @param  array<string, PRD_Product>  $products
     * @param  array<string, PRD_Product>  $sets
     */
    private function seedProductTags(array $tags, array $products, array $sets): void
    {
        $assignments = [
            'filadelfiya' => ['hit'],
            'kaliforniya' => ['new'],
            'kappa-maki' => ['vegan'],
            'nabor-dva-rolla' => ['hit', 'new'],
        ];

        foreach ($assignments as $productSlug => $tagCodes) {
            $product = $products[$productSlug] ?? $sets[$productSlug] ?? null;

            if ($product === null) {
                continue;
            }

            $tagIds = collect($tagCodes)
                ->map(fn (string $code): int => $tags[$code]->id)
                ->all();

            $product->tags()->sync($tagIds);
        }
    }
}
