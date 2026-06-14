<?php

namespace App\Providers;

use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Catalog\Repository\CategoryRepository;
use App\Domain\Catalog\Repository\TagRepository;
use App\Infrastructure\Catalog\Repository\EloquentCatalogItemRepository;
use App\Infrastructure\Catalog\Repository\EloquentCategoryRepository;
use App\Infrastructure\Catalog\Repository\EloquentTagRepository;
use Illuminate\Support\ServiceProvider;

final class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepository::class, EloquentCategoryRepository::class);
        $this->app->bind(CatalogItemRepository::class, EloquentCatalogItemRepository::class);
        $this->app->bind(TagRepository::class, EloquentTagRepository::class);
    }
}
