<?php

namespace App\View\Components\Front;

use App\Models\ProductCategory;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CategoryBar extends Component
{
    public $categories;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->categories = ProductCategory::where(
            'visible',
            1
        )->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.front.category-bar');
    }
}
