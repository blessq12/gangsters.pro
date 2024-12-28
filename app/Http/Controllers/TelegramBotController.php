<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TelegramBotController extends Controller
{
    /**
     * Получить информацию о компании
     */
    public function getCompany(): JsonResponse
    {
        $company = Company::with(['legals', 'workShedules'])->first();

        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Получить категории продуктов
     */
    public function getProductCategories(): JsonResponse
    {
        $categories = ProductCategory::where('visible', true)
            ->has('products')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Получить список продуктов
     */
    public function getProducts(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['imgs'])
            ->where('visible', true);

        if ($request->has('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
