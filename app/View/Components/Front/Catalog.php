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
                $product->images = $product->imgs->map(fn ($image) => '/uploads/' . $image->path);
                $product->thumbs = $product->imgs->map(fn ($image) => [
                    'small' => '/uploads/' . $image->thumbnail('sm', 'path'),
                    'medium' => '/uploads/' . $image->thumbnail('md', 'path'),
                    'large' => '/uploads/' . $image->thumbnail('lg', 'path'),
                ]);
            });
        });
        $products = $categories;
        return $products;
    }
}
