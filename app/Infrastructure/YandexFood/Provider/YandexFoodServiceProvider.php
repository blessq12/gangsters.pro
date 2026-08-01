<?php

namespace App\Infrastructure\YandexFood\Provider;

use App\Application\YandexFood\Port\YandexFoodAuthenticator;
use App\Application\YandexFood\Port\YandexFoodMenuCatalogPort;
use App\Infrastructure\YandexFood\Auth\ConfigYandexFoodAuthenticator;
use App\Infrastructure\YandexFood\Reader\YandexFoodCatalogReader;
use Illuminate\Support\ServiceProvider;

final class YandexFoodServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(YandexFoodAuthenticator::class, ConfigYandexFoodAuthenticator::class);
        $this->app->bind(YandexFoodMenuCatalogPort::class, YandexFoodCatalogReader::class);
    }
}
