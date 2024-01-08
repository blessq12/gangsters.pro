<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // $this->loadViewsFrom('','');
        Paginator::useBootstrapFive();
        View::composer(['errors::*', 'error.*'], function($view){
            $view->with('company', Company::first());
        });
        View::share('crmLinks', [
            (object)['name' => 'Кампания', 'route' => 'crm.companies.index', 'url' => '/crm/companies'],
            (object)['name' => 'Заказы', 'route' => 'crm.orders.index', 'url' => '/crm/orders'],
            (object)['name' => 'Пользователи', 'route' => 'crm.users.index', 'url' => '/crm/users'],
            (object)['name' => 'Вакансии', 'route' => 'crm.vacancies.index', 'url' => '/crm/vacancies'],
            (object)['name' => 'Товары', 'route' => 'crm.products.index', 'url' => '/crm/products/'],
            (object)['name' => 'Истории', 'route' => 'crm.stories.index', 'url' => '/crm/stories'],
            (object)['name' => 'Баннеры', 'route' => 'crm.banners.index', 'url' => '/crm/banners'],
        ]);
    }
}
