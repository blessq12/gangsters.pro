<?php

namespace App\View\Components\Front;

use App\Models\Company;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Navbar extends Component
{
    /**
     * Create a new component instance.
     */
    public $company;

    public function __construct()
    {
        $company = Company::first();
        $company->legals;
        $this->company = $company;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.front.navbar');
    }
}
