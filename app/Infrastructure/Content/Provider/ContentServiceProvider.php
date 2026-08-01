<?php

namespace App\Infrastructure\Content\Provider;

use App\Domain\Content\Port\MarketingMediaUrlPort;
use App\Domain\Content\Repository\BannerRepository;
use App\Domain\Content\Repository\CompanyDocumentRepository;
use App\Domain\Content\Repository\CompanyLegalRepository;
use App\Domain\Content\Repository\CompanyRepository;
use App\Domain\Content\Repository\DeliveryConfigurationRepository;
use App\Domain\Content\Repository\PromotionRepository;
use App\Infrastructure\Content\Port\MarketingMediaUrlAdapter;
use App\Infrastructure\Content\Repository\EloquentBannerRepository;
use App\Infrastructure\Content\Repository\EloquentCompanyDocumentRepository;
use App\Infrastructure\Content\Repository\EloquentCompanyLegalRepository;
use App\Infrastructure\Content\Repository\EloquentCompanyRepository;
use App\Infrastructure\Content\Repository\EloquentDeliveryConfigurationRepository;
use App\Infrastructure\Content\Repository\EloquentPromotionRepository;
use Illuminate\Support\ServiceProvider;

final class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepository::class, EloquentCompanyRepository::class);
        $this->app->bind(CompanyLegalRepository::class, EloquentCompanyLegalRepository::class);
        $this->app->bind(CompanyDocumentRepository::class, EloquentCompanyDocumentRepository::class);
        $this->app->bind(BannerRepository::class, EloquentBannerRepository::class);
        $this->app->bind(PromotionRepository::class, EloquentPromotionRepository::class);
        $this->app->bind(MarketingMediaUrlPort::class, MarketingMediaUrlAdapter::class);
        $this->app->bind(DeliveryConfigurationRepository::class, EloquentDeliveryConfigurationRepository::class);
    }
}
