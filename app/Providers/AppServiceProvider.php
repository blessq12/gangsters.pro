<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Два Vite-приложения: разные hot-файлы и каталоги артефактов.
        if ($this->app->runningInConsole()) {
            return;
        }

        if (request()->is('backoffice', 'backoffice/*')) {
            Vite::useHotFile(public_path('hot-backoffice'))
                ->useBuildDirectory('build/backoffice');

            return;
        }

        Vite::useHotFile(public_path('hot-public'))
            ->useBuildDirectory('build/public');
    }
}
