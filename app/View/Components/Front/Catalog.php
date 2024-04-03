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
        $categories = ProductCategory::has('products')->get();
        $products = $categories->each(fn ($category) => $category->products);
        return $products;
    }
}
