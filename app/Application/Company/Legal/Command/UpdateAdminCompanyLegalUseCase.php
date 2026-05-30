<?php

namespace App\Application\Company\Legal\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Company\Legal\DTO\UpdateCompanyLegalDto;
use App\Application\Company\Legal\Query\GetAdminCompanyLegalQuery;
use App\Domain\SystemContent\Entity\CompanyLegal;
use App\Domain\SystemContent\Repository\CompanyLegalRepository;
use App\Domain\SystemContent\Repository\CompanyRepository;

final class UpdateAdminCompanyLegalUseCase
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly CompanyLegalRepository $legals,
        private readonly GetAdminCompanyLegalQuery $legalQuery,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateCompanyLegalDto $dto): array
    {
        $company = $this->companies->first();
        if ($company === null) {
            throw new ApiException('Company not found.', 404);
        }

        $current = $this->legals->first();

        $legal = $current !== null
            ? $current->withAttributes(
                fullName: $dto->fullName,
                shortName: $dto->shortName,
                legalForm: $dto->legalForm,
                legalEmail: $dto->legalEmail,
                contractsEmail: $dto->contractsEmail,
                legalPhone: $dto->legalPhone,
                owner: $dto->owner,
                responsiblePerson: $dto->responsiblePerson,
                responsiblePosition: $dto->responsiblePosition,
                inn: $dto->inn,
                ogrn: $dto->ogrn,
                ogrnip: $dto->ogrnip,
                okpo: $dto->okpo,
                kpp: $dto->kpp,
                taxSystem: $dto->taxSystem,
                isVatPayer: $dto->isVatPayer,
                vatRateDefault: $dto->vatRateDefault,
                registrationAddress: $dto->registrationAddress,
                actualAddress: $dto->actualAddress,
                postalAddress: $dto->postalAddress,
                bankName: $dto->bankName,
                bik: $dto->bik,
                checkingAccount: $dto->checkingAccount,
                correspondentAccount: $dto->correspondentAccount,
            )
            : new CompanyLegal(
                id: 0,
                companyId: $company->id(),
                fullName: $dto->fullName,
                shortName: $dto->shortName,
                legalForm: $dto->legalForm,
                legalEmail: $dto->legalEmail,
                contractsEmail: $dto->contractsEmail,
                legalPhone: $dto->legalPhone,
                owner: $dto->owner,
                responsiblePerson: $dto->responsiblePerson,
                responsiblePosition: $dto->responsiblePosition,
                inn: $dto->inn,
                ogrn: $dto->ogrn,
                ogrnip: $dto->ogrnip,
                okpo: $dto->okpo,
                kpp: $dto->kpp,
                taxSystem: $dto->taxSystem,
                isVatPayer: $dto->isVatPayer,
                vatRateDefault: $dto->vatRateDefault,
                registrationAddress: $dto->registrationAddress,
                actualAddress: $dto->actualAddress,
                postalAddress: $dto->postalAddress,
                bankName: $dto->bankName,
                bik: $dto->bik,
                checkingAccount: $dto->checkingAccount,
                correspondentAccount: $dto->correspondentAccount,
            );

        $this->legals->save($legal);

        return $this->legalQuery->execute();
    }
}
