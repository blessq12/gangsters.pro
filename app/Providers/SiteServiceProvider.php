<?php

namespace App\Providers;

use App\Application\Site\Contracts\SiteSeoPagesRepository;
use App\Application\Site\SiteSeoResolver;
use App\Infrastructure\Site\Repository\JsonSiteSeoPagesRepository;
use Illuminate\Support\ServiceProvider;

final class SiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SiteSeoPagesRepository::class, JsonSiteSeoPagesRepository::class);
        $this->app->singleton(SiteSeoResolver::class);
    }
}
