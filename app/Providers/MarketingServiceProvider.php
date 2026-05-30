<?php

namespace App\Providers;

use App\Application\Marketing\Contracts\MarketingMediaStoragePort;
use App\Infrastructure\Marketing\Storage\LocalMarketingMediaStorage;
use Illuminate\Support\ServiceProvider;

class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MarketingMediaStoragePort::class, LocalMarketingMediaStorage::class);
    }

    public function boot(): void {}
}
