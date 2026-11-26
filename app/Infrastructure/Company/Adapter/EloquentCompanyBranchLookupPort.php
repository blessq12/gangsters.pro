<?php

namespace App\Infrastructure\Company\Adapter;

use App\Application\Branch\Presenter\BranchPresenter;
use App\Domain\Supporting\Branch\Repository\BranchRepository;
use App\Domain\Supporting\Company\Port\CompanyBranchLookupPort;

/**
 * ACL: Company ← Branch через порт.
 */
final class EloquentCompanyBranchLookupPort implements CompanyBranchLookupPort
{
    public function __construct(
        private readonly BranchRepository $branches,
        private readonly BranchPresenter $presenter,
    ) {}

    public function listSnapshotsByCompany(int $companyId): array
    {
        $out = [];
        foreach ($this->branches->listByCompany($companyId) as $branch) {
            $out[] = $this->presenter->branch($branch);
        }

        return $out;
    }

    public function listSummariesByCompany(int $companyId): array
    {
        $out = [];
        foreach ($this->branches->listByCompany($companyId) as $branch) {
            $out[] = $this->presenter->branchSummary($branch);
        }

        return $out;
    }
}
