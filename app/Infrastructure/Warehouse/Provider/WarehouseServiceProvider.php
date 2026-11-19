<?php

namespace App\Infrastructure\Warehouse\Provider;

use App\Domain\Supporting\Warehouse\Repository\IngredientRepository;
use App\Domain\Supporting\Warehouse\Repository\StockLevelRepository;
use App\Domain\Supporting\Warehouse\Repository\StockWriteOffRepository;
use App\Infrastructure\Warehouse\Repository\EloquentIngredientRepository;
use App\Infrastructure\Warehouse\Repository\EloquentStockLevelRepository;
use App\Infrastructure\Warehouse\Repository\EloquentStockWriteOffRepository;
use Illuminate\Support\ServiceProvider;

final class WarehouseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IngredientRepository::class, EloquentIngredientRepository::class);
        $this->app->bind(StockLevelRepository::class, EloquentStockLevelRepository::class);
        $this->app->bind(StockWriteOffRepository::class, EloquentStockWriteOffRepository::class);
    }
}
