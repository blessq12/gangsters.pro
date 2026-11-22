<?php

namespace App\Infrastructure\Company\Provider;

use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\BranchSetRepository;
use App\Domain\Supporting\Company\Repository\BranchTagRepository;
use App\Domain\Supporting\Company\Repository\CompanyDocumentRepository;
use App\Domain\Supporting\Company\Repository\CompanyLegalRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Repository\EloquentBranchCategoryRepository;
use App\Infrastructure\Company\Repository\EloquentBranchMenuRepository;
use App\Infrastructure\Company\Repository\EloquentBranchRepository;
use App\Infrastructure\Company\Repository\EloquentBranchSetRepository;
use App\Infrastructure\Company\Repository\EloquentBranchTagRepository;
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
        $this->app->bind(BranchRepository::class, EloquentBranchRepository::class);
        $this->app->bind(BranchMenuRepository::class, EloquentBranchMenuRepository::class);
        $this->app->bind(BranchCategoryRepository::class, EloquentBranchCategoryRepository::class);
        $this->app->bind(BranchTagRepository::class, EloquentBranchTagRepository::class);
        $this->app->bind(BranchSetRepository::class, EloquentBranchSetRepository::class);
    }
}
