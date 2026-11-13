<?php

namespace App\Infrastructure\Staff\Provider;

use App\Domain\Staff\Port\StaffSessionPort;
use App\Domain\Staff\Repository\EmployeeRepository;
use App\Infrastructure\Staff\Port\LaravelStaffSessionAdapter;
use App\Infrastructure\Staff\Repository\EloquentEmployeeRepository;
use Illuminate\Support\ServiceProvider;

final class StaffServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmployeeRepository::class, EloquentEmployeeRepository::class);
        $this->app->bind(StaffSessionPort::class, LaravelStaffSessionAdapter::class);
    }
}
