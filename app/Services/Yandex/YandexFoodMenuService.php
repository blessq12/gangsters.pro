<?php

namespace App\Services\Yandex;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\ProductCategory;

class YandexFoodMenuService
{
    public function getMenu(string $id)
    {
        return 'menu data';
    }

    public function getMenuComposition(string $id)
    {
        $categories = ProductCategory::with(['products', 'products.imgs'])->where('visible', 1)->get();

        $categories = $categories->filter(function ($category) {
            return $category->products()->count() > 0;
        });

        $categoriesOutput = $categories->map(function ($category) {
            return [
                'id' => (string) $category->id,
                'name' => $category->name,
                'parentId' => null,
                'sortOrder' => $category->order ?? 100,
                'images' => [],
            ];
        });

        $items = [];

        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                if ($product->price <= 0) continue;

                $items[] = [
                    'id' => (string) $product->id,
                    'categoryId' => (string) $product->categories()->first()->id,
                    'name' => $product->name,
                    'description' => $product->consist ?? '',
                    'price' => (float) $product->price,
                    'vat' => (int) ($product->vat ?? 0),
                    'isCatchweight' => false,
                    'measure' => (int) ($product->weight ?? 0),
                    'weightQuantum' => null,
                    'measureUnit' => 'г',
                    'sortOrder' => (int) ($product->order ?? 100),
                    'modifierGroups' => [],
                    'images' => $product->imgs ? $product->imgs->map(function ($image) {
                        return [
                            'hash' => sha1_file(public_path('uploads/' . $image->path)),
                            'url' => '/uploads/' . $image->path,
                        ];
                    })->toArray() : [],
                ];
            }
        }

        return [
            'categories' => $categoriesOutput,
            'items' => $items,
            'lastChange' => Carbon::now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    public function getMenuAvailability(string $id)
    {
        $unavailableProducts = Product::where('visible', 0)->get();

        return [
            'items' => $unavailableProducts->map(function ($product) {
                return [
                    'id' => (string) $product->id,
                    'quantity' => 0,
                ];
            })->toArray(),
            'modifiers' => [],
        ];
    }

    public function getMenuPromos(string $id)
    {
        return [
            'promoItems' => [],
        ];
    }
}
