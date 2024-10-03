<?php

namespace App\View\Components\Front;

use App\Models\ProductCategory;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Catalog extends Component
{
    public $goods;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->goods = $this->getProducts();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.front.catalog');
    }

    public function getProducts()
    {
        $categories = ProductCategory::where('visible', true)->has('products')->get();
        $categories->each(function ($category) {
            $category->products->each(function ($product) {
                $product->thumbnails = $product->thumbs ? $product->thumbs : null;
                $product->images = $product->images ? $product->images : null;
            });
        });
        $products = $categories;
        return $products;
    }
}
