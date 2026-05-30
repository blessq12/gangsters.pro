<?php

namespace App\Providers;

use App\Application\Company\Contracts\AdminUserRepository;
use App\Infrastructure\Company\EloquentAdminUserRepository;
use Illuminate\Support\ServiceProvider;

final class CompanyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminUserRepository::class, EloquentAdminUserRepository::class);
    }
}
