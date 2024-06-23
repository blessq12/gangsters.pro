<?php

namespace App\View\Components\Front;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Company;

class Navbar extends Component
{
    public Company $company;
    public $links;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->company = Company::first();
        $this->links = $this->getLinks();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.front.navbar');
    }

    public function getLinks(): array
    {
        return [
            (object) ['name' => 'О компании', 'route' => 'main.about'],
            (object) ['name' => 'Оплата и доставка', 'route' => 'main.purchaseAndDelivery'],
            (object) ['name' => 'Контакты', 'route' => 'main.contact'],
        ];
    }
}
