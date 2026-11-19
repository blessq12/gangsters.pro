<?php

namespace App\Application\Company\Query;

use App\Application\Company\Presenter\CompanyPresenter;
use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Entity\Company;
use App\Domain\Supporting\Company\Entity\Kitchen;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Supporting\Company\Repository\CompanyDocumentRepository;
use App\Domain\Supporting\Company\Repository\CompanyLegalRepository;
use App\Domain\Supporting\Company\Repository\CompanyRepository;
use App\Domain\Supporting\Company\Repository\KitchenRepository;

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
        private readonly KitchenRepository $kitchens,
        private readonly CompanyPresenter $presenter,
    ) {}

    /**
     * @return array{
     *     company: array<string, mixed>|null,
     *     legal: array<string, mixed>|null,
     *     documents: list<array<string, mixed>>,
     *     branch: array<string, mixed>|null,
     *     kitchen: array<string, mixed>|null
     * }
     */
    public function execute(): array
    {
        $company = $this->companies->findPublic();
        $companyId = $company?->id() ?? CompanyRepository::SINGLETON_ID;

        $branch = $this->resolvePublicBranch($companyId);
        $kitchen = $branch instanceof Branch
            ? $this->resolveKitchenForBranch($companyId, $branch->id())
            : null;

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
            'kitchen' => $kitchen instanceof Kitchen
                ? $this->presenter->kitchen($kitchen)
                : null,
        ];
    }

    private function resolvePublicBranch(int $companyId): ?Branch
    {
        foreach ($this->branches->listByCompany($companyId) as $branch) {
            if ($branch->isActive()) {
                return $branch;
            }
        }

        return null;
    }

    private function resolveKitchenForBranch(int $companyId, int $branchId): ?Kitchen
    {
        foreach ($this->kitchens->listByCompany($companyId) as $kitchen) {
            if ($kitchen->branchId() === $branchId) {
                return $kitchen;
            }
        }

        return null;
    }
}
