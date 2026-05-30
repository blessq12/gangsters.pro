<?php

namespace App\Providers;

use App\Application\Reporting\Query\BusinessMetricsReader;
use App\Application\Reporting\Query\ClientOrderSummaryReader;
use App\Infrastructure\Reporting\Query\EloquentBusinessMetricsReader;
use App\Infrastructure\Reporting\Query\EloquentClientOrderSummaryReader;
use Illuminate\Support\ServiceProvider;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BusinessMetricsReader::class, EloquentBusinessMetricsReader::class);
        $this->app->bind(ClientOrderSummaryReader::class, EloquentClientOrderSummaryReader::class);
    }

    public function boot(): void {}
}
