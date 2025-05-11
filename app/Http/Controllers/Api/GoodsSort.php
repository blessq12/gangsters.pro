<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GoodsSort extends Controller
{
    //
    public function updateCategoryOrder(Request $request)
    {
        $order = $request->input('order');

        foreach ($order as $item) {
            \App\Models\ProductCategory::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    public function updateProductOrder(Request $request)
    {
        $order = $request->input('order');
        $categoryId = $request->input('category_id');

        foreach ($order as $item) {
            \Illuminate\Support\Facades\DB::table('category_product')
                ->where('product_id', $item['id'])
                ->where('category_id', $categoryId)
                ->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    public function getAvailableProducts(Request $request)
    {
        $products = \App\Models\Product::all();

        if ($request->input('search')) {
            $products = \App\Models\Product::where('name', 'like', '%' . $request->input('search') . '%')->get();
        }

        foreach ($products as $product) {
            $product->thumbs = $product->thumbs;
        }

        return response()->json($products);
    }

    public function addProductToCategory(Request $request)
    {
        $productId = $request->input('product_id');
        $categoryId = $request->input('category_id');

        $product = \App\Models\Product::find($productId);
        $product->categories()->attach($categoryId);

        return response()->json(['success' => true]);
    }

    public function removeProductFromCategory(Request $request)
    {
        $productId = $request->input('product_id');
        $categoryId = $request->input('category_id');

        $product = \App\Models\Product::find($productId);
        $product->categories()->detach($categoryId);

        return response()->json(['success' => true]);
    }
}
