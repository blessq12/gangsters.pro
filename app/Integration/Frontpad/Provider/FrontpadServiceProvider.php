<?php

namespace App\Integration\Frontpad\Provider;

use App\Application\Order\Port\FrontpadStatusMapping;
use App\Infrastructure\Integration\Frontpad\FrontpadStatusMapper;
use App\Infrastructure\Integration\Frontpad\HttpFrontpadOrderExporter;
use App\Integration\Frontpad\FrontpadOrderExporter;
use Illuminate\Support\ServiceProvider;

final class FrontpadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FrontpadOrderExporter::class, HttpFrontpadOrderExporter::class);
        $this->app->bind(FrontpadStatusMapping::class, FrontpadStatusMapper::class);
    }
}
