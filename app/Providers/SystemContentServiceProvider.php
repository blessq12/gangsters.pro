<?php

namespace App\Providers;

use App\Domain\SystemContent\Ports\CompanyDeliveryTermsPort;
use App\Domain\SystemContent\Repository\BannerRepository as BannerRepositoryContract;
use App\Domain\SystemContent\Repository\CompanyLegalRepository as CompanyLegalRepositoryContract;
use App\Domain\SystemContent\Repository\CompanyRepository as CompanyRepositoryContract;
use App\Domain\SystemContent\Repository\DocumentRepository as DocumentRepositoryContract;
use App\Domain\SystemContent\Repository\PromotionRepository as PromotionRepositoryContract;
use App\Infrastructure\SystemContent\Media\StorageMediaUrlResolver;
use App\Infrastructure\SystemContent\Ports\CompanyDeliveryTermsPortImpl;
use App\Infrastructure\SystemContent\Repository\EloquentBannerRepository;
use App\Infrastructure\SystemContent\Repository\EloquentCompanyLegalRepository;
use App\Infrastructure\SystemContent\Repository\EloquentCompanyRepository;
use App\Infrastructure\SystemContent\Repository\EloquentDocumentRepository;
use App\Infrastructure\SystemContent\Repository\EloquentPromotionRepository;
use App\Shared\SystemContent\MediaUrlResolver;
use Illuminate\Support\ServiceProvider;

final class SystemContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BannerRepositoryContract::class, EloquentBannerRepository::class);
        $this->app->bind(PromotionRepositoryContract::class, EloquentPromotionRepository::class);
        $this->app->bind(CompanyRepositoryContract::class, EloquentCompanyRepository::class);
        $this->app->bind(CompanyDeliveryTermsPort::class, CompanyDeliveryTermsPortImpl::class);
        $this->app->bind(CompanyLegalRepositoryContract::class, EloquentCompanyLegalRepository::class);
        $this->app->bind(DocumentRepositoryContract::class, EloquentDocumentRepository::class);
        $this->app->bind(MediaUrlResolver::class, StorageMediaUrlResolver::class);
    }
}

