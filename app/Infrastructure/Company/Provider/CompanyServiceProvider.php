<?php

namespace App\Infrastructure\Company\Provider;

use App\Domain\Company\Repository\BranchRepository;
use App\Domain\Company\Repository\CompanyDocumentRepository;
use App\Domain\Company\Repository\CompanyLegalRepository;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\Company\Repository\KitchenRepository;
use App\Infrastructure\Company\Repository\EloquentBranchRepository;
use App\Infrastructure\Company\Repository\EloquentCompanyDocumentRepository;
use App\Infrastructure\Company\Repository\EloquentCompanyLegalRepository;
use App\Infrastructure\Company\Repository\EloquentCompanyRepository;
use App\Infrastructure\Company\Repository\EloquentKitchenRepository;
use Illuminate\Support\ServiceProvider;

final class CompanyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepository::class, EloquentCompanyRepository::class);
        $this->app->bind(CompanyLegalRepository::class, EloquentCompanyLegalRepository::class);
        $this->app->bind(CompanyDocumentRepository::class, EloquentCompanyDocumentRepository::class);
        $this->app->bind(BranchRepository::class, EloquentBranchRepository::class);
        $this->app->bind(KitchenRepository::class, EloquentKitchenRepository::class);
    }
}
