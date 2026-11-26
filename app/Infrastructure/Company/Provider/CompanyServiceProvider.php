<?php

namespace App\Infrastructure\Company\Provider;

use App\Domain\Supporting\Company\Port\CompanyBranchLookupPort;
use App\Domain\Supporting\Company\Repository\CompanyDocumentRepository;
use App\Domain\Supporting\Company\Repository\CompanyLegalRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Adapter\EloquentCompanyBranchLookupPort;
use App\Infrastructure\Company\Repository\EloquentCompanyDocumentRepository;
use App\Infrastructure\Company\Repository\EloquentCompanyLegalRepository;
use App\Infrastructure\Company\Repository\EloquentCompanyRepository;
use Illuminate\Support\ServiceProvider;

final class CompanyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepository::class, EloquentCompanyRepository::class);
        $this->app->bind(CompanyLegalRepository::class, EloquentCompanyLegalRepository::class);
        $this->app->bind(CompanyDocumentRepository::class, EloquentCompanyDocumentRepository::class);
        $this->app->bind(CompanyBranchLookupPort::class, EloquentCompanyBranchLookupPort::class);
    }
}
