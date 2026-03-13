<?php

namespace App\Providers;

use App\Domain\Category\Repository\CategoryRepository as CategoryRepositoryContract;
use App\Domain\Product\Repository\ProductRepository as ProductRepositoryContract;
use App\Infrastructure\Category\Repository\CategoryRepository as EloquentCategoryRepository;
use App\Infrastructure\Product\Repository\ProductRepository as EloquentProductRepository;
use Illuminate\Support\ServiceProvider;

class ProductDomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryContract::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryContract::class, EloquentCategoryRepository::class);
    }

    public function boot(): void
    {
    }
}

