<?php

namespace App\Providers;

use App\Domain\SystemContent\Repository\BannerRepository as BannerRepositoryContract;
use App\Domain\SystemContent\Repository\PromotionRepository as PromotionRepositoryContract;
use App\Infrastructure\SystemContent\Media\StorageMediaUrlResolver;
use App\Infrastructure\SystemContent\Repository\EloquentBannerRepository;
use App\Infrastructure\SystemContent\Repository\EloquentPromotionRepository;
use App\Shared\SystemContent\MediaUrlResolver;
use Illuminate\Support\ServiceProvider;

final class SystemContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BannerRepositoryContract::class, EloquentBannerRepository::class);
        $this->app->bind(PromotionRepositoryContract::class, EloquentPromotionRepository::class);
        $this->app->bind(MediaUrlResolver::class, StorageMediaUrlResolver::class);
    }
}

