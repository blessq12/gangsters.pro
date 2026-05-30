<?php

namespace App\Application\Company\Profile\Query;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Profile\Presenter\AdminCompanyProfilePresenter;
use App\Domain\SystemContent\Repository\CompanyRepository;

final class GetAdminCompanyProfileQuery
{
    public function __construct(
        private readonly CompanyRepository $companies,
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

        return AdminCompanyProfilePresenter::present($company);
    }
}
