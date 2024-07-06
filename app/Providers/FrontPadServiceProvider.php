<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FrontPadServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('frontpad', function () {
            return new \App\Services\FrontpadService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
