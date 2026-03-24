<?php

namespace App\Application\SystemContent\Query;

use App\Domain\SystemContent\Repository\CompanyRepository;

final class GetSystemCompanyUseCase
{
    public function __construct(
        private readonly CompanyRepository $companies,
    ) {
    }

    public function execute(): array
    {
        $company = $this->companies->first();

        if ($company === null) {
            return ['data' => null];
        }

        return [
            'data' => [
                'id' => $company->id(),
                'name' => $company->name(),
                'description' => $company->description(),
                'country' => $company->country(),
                'state' => $company->state(),
                'city' => $company->city(),
                'street' => $company->street(),
                'house' => $company->house(),
                'phone' => $company->phone(),
                'phone_additional' => $company->phoneAdditional(),
                'email_address' => $company->emailAddress(),
                'logo' => $company->logo(),
            ],
        ];
    }
}

