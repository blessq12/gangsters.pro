<?php

namespace App\Providers;

use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use App\Infrastructure\Shopping\Repository\ShoppingSessionRepository;
use Illuminate\Support\ServiceProvider;

class ShoppingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ShoppingSessionRepositoryInterface::class, ShoppingSessionRepository::class);
    }

    public function boot(): void
    {
    }
}
