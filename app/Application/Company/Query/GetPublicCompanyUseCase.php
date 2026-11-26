<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\CompanyPresenter;
use App\Domain\Supporting\Company\Entity\Company;
use App\Domain\Supporting\Company\Port\CompanyBranchLookupPort;
use App\Domain\Supporting\Company\Repository\CompanyDocumentRepository;
use App\Domain\Supporting\Company\Repository\CompanyLegalRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;

/**
 * Публичный срез компании для витрины.
 */
final class GetPublicCompanyUseCase
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly CompanyLegalRepository $legal,
        private readonly CompanyDocumentRepository $documents,
        private readonly CompanyBranchLookupPort $branches,
        private readonly CompanyPresenter $presenter,
    ) {}

    /**
     * @return array{
     *     company: array<string, mixed>|null,
     *     legal: array<string, mixed>|null,
     *     documents: list<array<string, mixed>>,
     *     branch: array<string, mixed>|null,
     *     branches: list<array{id: int, name: string, is_active: bool, phone: string|null}>
     * }
     */
    public function execute(): array
    {
        $company = $this->companies->findPublic();
        $companyId = $company?->id() ?? CompanyRepository::SINGLETON_ID;

        $summaries = $this->branches->listSummariesByCompany($companyId);
        $snapshots = $this->branches->listSnapshotsByCompany($companyId);
        $branch = $this->resolvePublicBranch($snapshots);

        return [
            'company' => $company instanceof Company
                ? $this->presenter->company($company)
                : null,
            'legal' => $this->presenter->legal($this->legal->findPublic()),
            'documents' => array_map(
                fn ($document) => $this->presenter->document($document),
                $this->documents->findAllOrdered(),
            ),
            'branch' => $branch,
            'branches' => $summaries,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $companyBranches
     * @return array<string, mixed>|null
     */
    private function resolvePublicBranch(array $companyBranches): ?array
    {
        foreach ($companyBranches as $branch) {
            if (($branch['is_active'] ?? false) === true) {
                return $branch;
            }
        }

        return null;
    }
}
