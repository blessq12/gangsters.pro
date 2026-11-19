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
 * Админский срез компании: всё, включая неактивные филиалы.
 */
final class GetAdminCompanyUseCase
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
     *     kitchen: array<string, mixed>|null,
     *     branches: list<array<string, mixed>>,
     *     kitchens: list<array<string, mixed>>
     * }
     */
    public function execute(): array
    {
        $company = $this->companies->findPublic();
        $companyId = $company?->id() ?? CompanyRepository::SINGLETON_ID;

        $allBranches = $this->branches->listByCompany($companyId);
        $allKitchens = $this->kitchens->listByCompany($companyId);

        $primaryBranch = $this->resolvePrimaryBranch($allBranches);
        $primaryKitchen = $primaryBranch instanceof Branch
            ? $this->resolveKitchenForBranch($allKitchens, $primaryBranch->id())
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
            'branch' => $primaryBranch instanceof Branch
                ? $this->presenter->branch($primaryBranch)
                : null,
            'kitchen' => $primaryKitchen instanceof Kitchen
                ? $this->presenter->kitchen($primaryKitchen)
                : null,
            'branches' => array_map(
                fn (Branch $branch) => $this->presenter->branch($branch),
                $allBranches,
            ),
            'kitchens' => array_map(
                fn (Kitchen $kitchen) => $this->presenter->kitchen($kitchen),
                $allKitchens,
            ),
        ];
    }

    /**
     * @param  list<Branch>  $branches
     */
    private function resolvePrimaryBranch(array $branches): ?Branch
    {
        foreach ($branches as $branch) {
            if ($branch->isActive()) {
                return $branch;
            }
        }

        return $branches[0] ?? null;
    }

    /**
     * @param  list<Kitchen>  $kitchens
     */
    private function resolveKitchenForBranch(array $kitchens, int $branchId): ?Kitchen
    {
        foreach ($kitchens as $kitchen) {
            if ($kitchen->branchId() === $branchId) {
                return $kitchen;
            }
        }

        return null;
    }
}
