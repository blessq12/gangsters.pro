<?php

namespace App\Providers;

use App\Application\Catalog\Contracts\AdminProductReadRepository;
use App\Application\Catalog\Contracts\ProductImageStoragePort;
use App\Application\Catalog\Contracts\TagDictionaryRepository;
use App\Infrastructure\Catalog\Repository\AdminProductReadRepository as EloquentAdminProductReadRepository;
use App\Infrastructure\Catalog\Repository\TagDictionaryRepository as EloquentTagDictionaryRepository;
use App\Infrastructure\Catalog\Storage\LocalProductImageStorage;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminProductReadRepository::class, EloquentAdminProductReadRepository::class);
        $this->app->bind(TagDictionaryRepository::class, EloquentTagDictionaryRepository::class);
        $this->app->bind(ProductImageStoragePort::class, LocalProductImageStorage::class);
    }

    public function boot(): void
    {
    }
}
