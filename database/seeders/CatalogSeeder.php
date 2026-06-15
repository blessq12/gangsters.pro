<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Enum\ProductStatus;
use App\Infrastructure\Catalog\Model\PRD_Category;
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

            $this->attachCategoryItems($categories, $products);
            $this->attachProductTags($tags, $products);
            $this->seedSetLines($products);
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
            'spicy' => ['label' => 'Острое', 'color' => 'rose', 'sort_order' => 2],
        ];

        $tags = [];

        foreach ($definitions as $code => $attributes) {
            $tags[$code] = PRD_Tag::query()->updateOrCreate(
                ['code' => $code],
                [
                    'label' => $attributes['label'],
                    'color' => $attributes['color'],
                    'sort_order' => $attributes['sort_order'],
                    'is_active' => true,
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
            'nabori' => ['name' => 'Наборы', 'sort_order' => 1],
            'dopolneniya' => ['name' => 'Дополнения', 'sort_order' => 2],
            'napitki' => ['name' => 'Напитки', 'sort_order' => 3],
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
            'filadelfia' => [
                'name' => 'Филадельфия',
                'sku' => 'ROLL-001',
                'price' => 420,
                'description' => 'Лосось, сливочный сыр, огурец.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => true,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => false,
            ],
            'kalifornia' => [
                'name' => 'Калифорния',
                'sku' => 'ROLL-002',
                'price' => 390,
                'description' => 'Краб, авокадо, огурец, икра тобiko.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => true,
                'meta_gift_candidate' => true,
                'meta_is_complement_set' => false,
            ],
            'dragon' => [
                'name' => 'Дракон',
                'sku' => 'ROLL-003',
                'price' => 450,
                'description' => 'Угорь, авокадо, соус унаги.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => true,
                'meta_gift_candidate' => true,
                'meta_is_complement_set' => false,
            ],
            'kapuchin-roll' => [
                'name' => 'Капучино',
                'sku' => 'ROLL-004',
                'price' => 380,
                'description' => 'Креветка темпура, сливочный сыр.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => true,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => false,
            ],
            'ostryi-tunets' => [
                'name' => 'Острый тунец',
                'sku' => 'ROLL-005',
                'price' => 410,
                'description' => 'Тунец, спайси-соус, зелёный лук.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => true,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => false,
            ],
            'gift-filadelfia' => [
                'name' => 'Филадельфия (подарок)',
                'sku' => 'GIFT-001',
                'price' => 420,
                'description' => 'Подарочный ролл для акции.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => true,
                'meta_gift_candidate' => true,
                'meta_is_complement_set' => false,
            ],
            'complement-classic' => [
                'name' => 'Соевый соус + имбирь + васаби',
                'sku' => 'COMP-001',
                'price' => 49,
                'description' => 'Стандартный комплект дополнений.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => false,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => true,
            ],
            'complement-premium' => [
                'name' => 'Премиум-комплект',
                'sku' => 'COMP-002',
                'price' => 79,
                'description' => 'Расширенный комплект дополнений.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => false,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => true,
            ],
            'cola-033' => [
                'name' => 'Coca-Cola 0.33',
                'sku' => 'DRINK-001',
                'price' => 120,
                'description' => 'Газированный напиток.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => false,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => false,
            ],
            'mors-klyukva' => [
                'name' => 'Морс клюква 0.5',
                'sku' => 'DRINK-002',
                'price' => 150,
                'description' => 'Домашний морс.',
                'catalog_kind' => CatalogItemKind::Product->value,
                'meta_counts_as_roll' => false,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => false,
            ],
            'set-duet' => [
                'name' => 'Набор «Дуэт»',
                'sku' => 'SET-001',
                'price' => 750,
                'description' => 'Два классических ролла.',
                'catalog_kind' => CatalogItemKind::Set->value,
                'meta_counts_as_roll' => false,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => false,
            ],
            'set-semeinyi' => [
                'name' => 'Набор «Семейный»',
                'sku' => 'SET-002',
                'price' => 1_650,
                'description' => 'Четыре ролла и два напитка.',
                'catalog_kind' => CatalogItemKind::Set->value,
                'meta_counts_as_roll' => false,
                'meta_gift_candidate' => false,
                'meta_is_complement_set' => false,
            ],
        ];

        $products = [];

        foreach ($definitions as $slug => $attributes) {
            $products[$slug] = PRD_Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $attributes['name'],
                    'sku' => $attributes['sku'],
                    'description' => $attributes['description'],
                    'status' => ProductStatus::Active->value,
                    'catalog_kind' => $attributes['catalog_kind'],
                    'price' => $attributes['price'],
                    'calories' => 180,
                    'proteins' => 8,
                    'fats' => 6,
                    'carbs' => 22,
                    'nutrition_basis' => 'per_100g',
                    'ingredients' => ['рис', 'нори', 'рыба'],
                    'meta_counts_as_roll' => $attributes['meta_counts_as_roll'],
                    'meta_gift_candidate' => $attributes['meta_gift_candidate'],
                    'meta_is_complement_set' => $attributes['meta_is_complement_set'],
                    'archived_at' => null,
                ],
            );
        }

        return $products;
    }

    /**
     * @param  array<string, PRD_Category>  $categories
     * @param  array<string, PRD_Product>  $products
     */
    private function attachCategoryItems(array $categories, array $products): void
    {
        $assignments = [
            'rolly' => [
                'filadelfia' => 0,
                'kalifornia' => 1,
                'dragon' => 2,
                'kapuchin-roll' => 3,
                'ostryi-tunets' => 4,
                'gift-filadelfia' => 5,
            ],
            'nabori' => [
                'set-duet' => 0,
                'set-semeinyi' => 1,
            ],
            'dopolneniya' => [
                'complement-classic' => 0,
                'complement-premium' => 1,
            ],
            'napitki' => [
                'cola-033' => 0,
                'mors-klyukva' => 1,
            ],
        ];

        foreach ($assignments as $categorySlug => $items) {
            $category = $categories[$categorySlug];
            $sync = [];

            foreach ($items as $productSlug => $sortOrder) {
                $sync[$products[$productSlug]->id] = ['sort_order' => $sortOrder];
            }

            $category->products()->sync($sync);
        }
    }

    /**
     * @param  array<string, PRD_Tag>  $tags
     * @param  array<string, PRD_Product>  $products
     */
    private function attachProductTags(array $tags, array $products): void
    {
        $assignments = [
            'filadelfia' => ['hit'],
            'dragon' => ['new'],
            'ostryi-tunets' => ['spicy', 'hit'],
            'set-duet' => ['hit'],
            'set-semeinyi' => ['new'],
        ];

        foreach ($assignments as $productSlug => $tagCodes) {
            $tagIds = array_map(
                static fn (string $code): int => $tags[$code]->id,
                $tagCodes,
            );

            $products[$productSlug]->tags()->sync($tagIds);
        }
    }

    /**
     * @param  array<string, PRD_Product>  $products
     */
    private function seedSetLines(array $products): void
    {
        $definitions = [
            'set-duet' => [
                ['product' => 'filadelfia', 'quantity' => 1, 'sort_order' => 0],
                ['product' => 'kalifornia', 'quantity' => 1, 'sort_order' => 1],
            ],
            'set-semeinyi' => [
                ['product' => 'filadelfia', 'quantity' => 2, 'sort_order' => 0],
                ['product' => 'dragon', 'quantity' => 2, 'sort_order' => 1],
                ['product' => 'cola-033', 'quantity' => 2, 'sort_order' => 2],
            ],
        ];

        foreach ($definitions as $setSlug => $lines) {
            $setId = $products[$setSlug]->id;

            foreach ($lines as $line) {
                PRD_ProductSetLine::query()->updateOrCreate(
                    [
                        'set_id' => $setId,
                        'product_id' => $products[$line['product']]->id,
                    ],
                    [
                        'quantity' => $line['quantity'],
                        'sort_order' => $line['sort_order'],
                    ],
                );
            }
        }
    }
}
