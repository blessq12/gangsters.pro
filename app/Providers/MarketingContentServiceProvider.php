<?php

namespace App\Providers;

use App\Domain\MarketingContent\Port\MarketingMediaUrlPort;
use App\Domain\MarketingContent\Repository\BannerRepository;
use App\Domain\MarketingContent\Repository\PromotionRepository;
use App\Infrastructure\MarketingContent\Port\MarketingMediaUrlAdapter;
use App\Infrastructure\MarketingContent\Repository\EloquentBannerRepository;
use App\Infrastructure\MarketingContent\Repository\EloquentPromotionRepository;
use Illuminate\Support\ServiceProvider;

final class MarketingContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BannerRepository::class, EloquentBannerRepository::class);
        $this->app->bind(PromotionRepository::class, EloquentPromotionRepository::class);
        $this->app->bind(MarketingMediaUrlPort::class, MarketingMediaUrlAdapter::class);
    }
}
