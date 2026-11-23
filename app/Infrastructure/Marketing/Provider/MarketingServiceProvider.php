<?php

namespace App\Infrastructure\Marketing\Provider;

use App\Domain\Supporting\Company\Repository\DeliveryConditionsTextRepository;
use App\Domain\Supporting\Marketing\Port\MarketingDeliveryConditionsPort;
use App\Domain\Supporting\Marketing\Repository\BannerRepository;
use App\Domain\Supporting\Marketing\Repository\CampaignRepository;
use App\Infrastructure\Marketing\Adapter\EloquentMarketingDeliveryConditionsPort;
use App\Infrastructure\Marketing\Repository\EloquentBannerRepository;
use App\Infrastructure\Marketing\Repository\EloquentDeliveryConditionsRepository;
use App\Infrastructure\Marketing\Repository\EloquentPromotionRepository;
use Illuminate\Support\ServiceProvider;

final class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BannerRepository::class, EloquentBannerRepository::class);
        $this->app->bind(CampaignRepository::class, EloquentPromotionRepository::class);
        $this->app->bind(DeliveryConditionsTextRepository::class, EloquentDeliveryConditionsRepository::class);
        $this->app->bind(MarketingDeliveryConditionsPort::class, EloquentMarketingDeliveryConditionsPort::class);
    }
}
