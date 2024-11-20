<?php

namespace App\View\Components\Front;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Company;

class Footer extends Component
{
    public $company;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->company = Company::first();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.front.footer');
    }
}
