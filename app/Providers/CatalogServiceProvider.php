<?php

namespace App\Providers;

use App\Application\Catalog\Contracts\AdminProductReadRepository;
use App\Application\Catalog\Contracts\CategoryDeletionGuardPort;
use App\Application\Catalog\Contracts\ProductDeletionGuardPort;
use App\Application\Catalog\Contracts\ProductImageStoragePort;
use App\Application\Catalog\Contracts\TagDeletionGuardPort;
use App\Application\Catalog\Contracts\TagDictionaryRepository;
use App\Infrastructure\Catalog\Guard\EloquentCategoryDeletionGuard;
use App\Infrastructure\Catalog\Guard\EloquentProductDeletionGuard;
use App\Infrastructure\Catalog\Guard\EloquentTagDeletionGuard;
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
        $this->app->bind(ProductDeletionGuardPort::class, EloquentProductDeletionGuard::class);
        $this->app->bind(CategoryDeletionGuardPort::class, EloquentCategoryDeletionGuard::class);
        $this->app->bind(TagDeletionGuardPort::class, EloquentTagDeletionGuard::class);
    }

    public function boot(): void
    {
    }
}
