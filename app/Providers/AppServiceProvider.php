<?php

namespace App\Providers;

use App\Support\Site\SitePublicConfigPresenter;
use App\Support\Site\SiteSeoResolver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('app', function ($view): void {
            $resolver = app(SiteSeoResolver::class);

            $view->with([
                'pageSeo' => $resolver->resolveForPath((string) request()->path()),
                'sitePublic' => app(SitePublicConfigPresenter::class)->forClient(),
            ]);
        });
    }
}
