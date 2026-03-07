<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Список категорий каталога.
     */
    public function categories()
    {
        $categories = ProductCategory::query()
            ->orderBy('name')
            ->get(['id', 'uri', 'name']);

        return response()->json(['categories' => $categories]);
    }

    /**
     * Список товаров с опциональным фильтром по категории.
     * Query: category_id (int) — id категории.
     */
    public function products(Request $request)
    {
        $query = Product::query()
            ->where('visible', true)
            ->whereNotNull('price')
            ->whereNotNull('weight');

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('product_categories.id', (int) $request->category_id);
            });
        }

        $products = $query->with(['imgs', 'categories:id,uri,name'])
            ->orderBy('order')
            ->get();

        $data = $products->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'consist' => $product->consist,
                'weight' => $product->weight,
                'price' => $product->price,
                'sku' => $product->sku,
                'images' => $product->imgs->map(fn ($img) => '/uploads/' . $img->path)->values()->all(),
                'categories' => $product->categories->map(fn ($c) => ['id' => $c->id, 'uri' => $c->uri, 'name' => $c->name])->values()->all(),
            ];
        });

        return response()->json(['products' => $data]);
    }
}
