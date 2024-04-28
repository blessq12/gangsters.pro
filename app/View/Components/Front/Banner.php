<?php

namespace App\View\Components\Front;

use App\Models\Banner as ModelsBanner;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Banner extends Component
{
    public $banners;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->banners = ModelsBanner::all();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.front.banner', ['banners' => $this->banners]);
    }
}
