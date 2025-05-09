<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\Product;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function index(Content $content)
    {
        $categories = ProductCategory::orderBy('order')->get();
        foreach ($categories as $category) {
            $category->products = $category->products()->orderBy('category_product.order')->get();
        }

        return $content
            ->title('Каталог')
            ->body(view('admin.catalog', [
                'categories' => $categories,
            ]));
    }
}
