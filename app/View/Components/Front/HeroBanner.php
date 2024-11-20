<?php

namespace App\View\Components\Front;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HeroBanner extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $title = 'Нет названия',
        public $description = 'Нет описания',
        public $banner = '/images/placeholder/empty-big.jpg',
        public $overlay = false,
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.front.hero-banner');
    }
}
