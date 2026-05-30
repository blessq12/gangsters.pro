<?php

namespace App\Application\Company\Legal\Query;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Legal\Presenter\AdminCompanyLegalPresenter;
use App\Domain\SystemContent\Repository\CompanyLegalRepository;
use App\Domain\SystemContent\Repository\CompanyRepository;

final class GetAdminCompanyLegalQuery
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly CompanyLegalRepository $legals,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $company = $this->companies->first();
        if ($company === null) {
            throw new ApiException('Company not found.', 404);
        }

        return AdminCompanyLegalPresenter::present(
            $this->legals->first(),
            $company->id(),
        );
    }
}
