<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\Company as CompanyEntity;
use App\Domain\SystemContent\Repository\CompanyRepository;
use App\Infrastructure\SystemContent\Model\SYS_Company;

final class EloquentCompanyRepository implements CompanyRepository
{
    public function first(): ?CompanyEntity
    {
        $company = SYS_Company::query()->first();

        if ($company === null) {
            return null;
        }

        return new CompanyEntity(
            id: (int) $company->id,
            name: $company->name,
            description: $company->description,
            country: $company->country,
            state: $company->state,
            city: $company->city,
            street: $company->street,
            house: $company->house,
            phone: $company->phone,
            phoneAdditional: $company->phone_additional,
            emailAddress: $company->email_address,
            logo: $company->logo,
        );
    }
}

