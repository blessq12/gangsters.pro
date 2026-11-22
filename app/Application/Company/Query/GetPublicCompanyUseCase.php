<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\CompanyPresenter;
use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Entity\Company;
use App\Domain\Supporting\Company\Repository\BranchRepository;
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
        private readonly BranchRepository $branches,
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

        $companyBranches = $this->branches->listByCompany($companyId);
        $branch = $this->resolvePublicBranch($companyBranches);

        return [
            'company' => $company instanceof Company
                ? $this->presenter->company($company)
                : null,
            'legal' => $this->presenter->legal($this->legal->findPublic()),
            'documents' => array_map(
                fn ($document) => $this->presenter->document($document),
                $this->documents->findAllOrdered(),
            ),
            'branch' => $branch instanceof Branch
                ? $this->presenter->branch($branch)
                : null,
            'branches' => array_map(
                fn (Branch $item): array => $this->presenter->branchSummary($item),
                $companyBranches,
            ),
        ];
    }

    /**
     * @param  list<Branch>  $companyBranches
     */
    private function resolvePublicBranch(array $companyBranches): ?Branch
    {
        foreach ($companyBranches as $branch) {
            if ($branch->isActive()) {
                return $branch;
            }
        }

        return null;
    }
}
