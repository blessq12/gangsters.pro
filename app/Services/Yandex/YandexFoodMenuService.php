<?php

namespace App\Services\Yandex;

class YandexFoodMenuService
{
    public function getMenu(string $id)
    {
        return 'menu data';
    }

    public function getMenuComposition(string $id)
    {
        $categories = \App\Models\ProductCategory::with('products')->where('visible', 1)->get();

        $categories = $categories->filter(function ($category) {
            return $category->products()->count() > 0;
        });
        $items = [];

        $categoriesOutput = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'images' => '',
                'parent_id' => null,
                'schedules' => [],
                'sortOrder' => $category->order,
            ];
        });

        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                $items[] = [
                    'id' => $product->id,
                    'categoryId' => $product->categories()->first()->id,
                    'name' => $product->name,
                    'description' => $product->description ?? '',
                    'price' => $product->price ?? 0,
                    'vat' => $product->vat ?? 0,
                    'isCatchweight' => $product->isCatchweight ?? false,
                    'measure' => $product->weight,
                    'weightQuantum' => $product->weightQuantum ?? 0,
                    'measureUnit' => 'г',
                    'excise' => $product->excise ?? '',
                    'nutrients' => $product->nutrients ?? [],
                    'sortOrder' => $product->sortOrder ?? 0,
                    'modifierGroups' => $product->modifierGroups ?? [],
                    'images' => $product->images->map(function ($image) {
                        return [
                            'hash' => sha1_file(public_path($image)),
                            'url' => $image,
                        ];
                    }) ?? [],
                    'additional_descriptions' => !empty($product->additional_descriptions)
                        ? $product->additional_descriptions
                        : new \stdClass(),
                ];
            }
        }

        return [
            'schedules' => [],
            'categories' => $categoriesOutput,
            'items' => $items,
            'lastChange' => \Carbon\Carbon::now()->setTimezone('UTC')->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    public function getMenuAvailability(string $id)
    {
        return [
            'items' => [],
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
