<?php

namespace App\Infrastructure\Marketing\Provider;

use App\Domain\Marketing\Repository\BannerRepository;
use App\Domain\Marketing\Repository\PromotionRepository;
use App\Domain\Marketing\Repository\StorefrontDisplayPriceRepository;
use App\Infrastructure\Marketing\Repository\EloquentBannerRepository;
use App\Infrastructure\Marketing\Repository\EloquentPromotionRepository;
use App\Infrastructure\Marketing\Repository\EloquentStorefrontDisplayPriceRepository;
use Illuminate\Support\ServiceProvider;

final class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BannerRepository::class, EloquentBannerRepository::class);
        $this->app->bind(PromotionRepository::class, EloquentPromotionRepository::class);
        $this->app->bind(StorefrontDisplayPriceRepository::class, EloquentStorefrontDisplayPriceRepository::class);
    }
}
