<?php

namespace App\Providers;

use App\Application\Company\Contracts\AdminUserRepository;
use App\Application\Company\Contracts\CompanyLogoStoragePort;
use App\Infrastructure\Company\EloquentAdminUserRepository;
use App\Infrastructure\Company\Storage\LocalCompanyLogoStorage;
use Illuminate\Support\ServiceProvider;

final class CompanyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminUserRepository::class, EloquentAdminUserRepository::class);
        $this->app->bind(CompanyLogoStoragePort::class, LocalCompanyLogoStorage::class);
    }
}
