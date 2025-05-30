<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchGoods(Request $request)
    {
        $query = $request->get('q', '');
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $products = \App\Models\Product::where('visible', 1)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('consist', 'LIKE', '%' . $query . '%');
            })
            // ->select(['id', 'name', 'consist', 'price', 'weight', ])
            ->limit(10)
            ->get();

        $products->each(function ($product) {
            $product->images = $product->thumbs();
        });

        return response()->json($products);
    }
}
