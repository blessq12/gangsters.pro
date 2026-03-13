<?php

namespace Database\Seeders;

use App\Infrastructure\Category\Model\PRD_Category;
use App\Infrastructure\Category\Model\PRD_CategoryProduct;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Product\Model\PRD_ProductImage;
use App\Infrastructure\Product\Model\PRD_ProductIngredient;
use App\Infrastructure\Product\Model\PRD_ProductPrice;
use App\Infrastructure\Product\Model\PRD_ProductTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PRDDemoCatalogSeeder extends Seeder
{
    /**
     * Seed demo catalog data for PRD_ domain.
     */
    public function run(): void
    {
        // Категории
        $rolls = PRD_Category::create([
            'name' => 'Роллы',
            'slug' => 'rolls',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $sets = PRD_Category::create([
            'name' => 'Сеты',
            'slug' => 'sets',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Общие нутриенты для примера
        $nutritionBase = [
            'calories' => 250,
            'proteins' => 8,
            'fats' => 10,
            'carbs' => 30,
            'nutrition_basis' => 'per_100g',
        ];

        // Продукты
        $california = PRD_Product::create(array_merge([
            'name' => 'Калифорния',
            'slug' => Str::slug('Калифорния'),
            'description' => 'Ролл Калифорния с крабом и огурцом.',
            'status' => 'active',
        ], $nutritionBase));

        $philadelphia = PRD_Product::create(array_merge([
            'name' => 'Филадельфия',
            'slug' => Str::slug('Филадельфия'),
            'description' => 'Классический ролл Филадельфия с лососем и сливочным сыром.',
            'status' => 'active',
        ], $nutritionBase));

        $setPopular = PRD_Product::create(array_merge([
            'name' => 'Сет “Популярный”',
            'slug' => Str::slug('Сет Популярный'),
            'description' => 'Набор из топовых роллов для компании.',
            'status' => 'active',
        ], $nutritionBase));

        // Изображения (демо-пути)
        foreach ([$california, $philadelphia, $setPopular] as $index => $product) {
            PRD_ProductImage::create([
                'product_id' => $product->id,
                'sort_order' => 1,
                'thumb_path' => "/images/demo/product-" . ($index + 1) . "-thumb.jpg",
                'thumb_width' => 200,
                'thumb_height' => 200,
                'medium_path' => "/images/demo/product-" . ($index + 1) . "-medium.jpg",
                'medium_width' => 600,
                'medium_height' => 400,
                'large_path' => "/images/demo/product-" . ($index + 1) . "-large.jpg",
                'large_width' => 1200,
                'large_height' => 800,
            ]);
        }

        // Состав
        PRD_ProductIngredient::create([
            'product_id' => $california->id,
            'name' => 'Рис',
        ]);
        PRD_ProductIngredient::create([
            'product_id' => $california->id,
            'name' => 'Краб',
        ]);
        PRD_ProductIngredient::create([
            'product_id' => $philadelphia->id,
            'name' => 'Рис',
        ]);
        PRD_ProductIngredient::create([
            'product_id' => $philadelphia->id,
            'name' => 'Лосось',
            'is_allergen' => true,
        ]);

        // Теги
        PRD_ProductTag::create([
            'product_id' => $california->id,
            'code' => 'popular',
        ]);
        PRD_ProductTag::create([
            'product_id' => $philadelphia->id,
            'code' => 'kids_friendly',
        ]);
        PRD_ProductTag::create([
            'product_id' => $setPopular->id,
            'code' => 'popular',
        ]);

        // Цены (regular)
        PRD_ProductPrice::create([
            'product_id' => $california->id,
            'amount' => 49900,
            'customer_status' => 'regular',
            'is_default' => true,
        ]);
        PRD_ProductPrice::create([
            'product_id' => $philadelphia->id,
            'amount' => 54900,
            'customer_status' => 'regular',
            'is_default' => true,
        ]);
        PRD_ProductPrice::create([
            'product_id' => $setPopular->id,
            'amount' => 149900,
            'customer_status' => 'regular',
            'is_default' => true,
        ]);

        // Связки категория–товар с порядком
        PRD_CategoryProduct::create([
            'category_id' => $rolls->id,
            'product_id' => $california->id,
            'sort_order' => 1,
        ]);
        PRD_CategoryProduct::create([
            'category_id' => $rolls->id,
            'product_id' => $philadelphia->id,
            'sort_order' => 2,
        ]);

        PRD_CategoryProduct::create([
            'category_id' => $sets->id,
            'product_id' => $setPopular->id,
            'sort_order' => 1,
        ]);
        PRD_CategoryProduct::create([
            'category_id' => $sets->id,
            'product_id' => $california->id,
            'sort_order' => 2,
        ]);
    }
}

