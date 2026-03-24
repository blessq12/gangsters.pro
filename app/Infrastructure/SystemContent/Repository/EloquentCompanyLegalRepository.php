<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\CompanyLegal as CompanyLegalEntity;
use App\Domain\SystemContent\Repository\CompanyLegalRepository;
use App\Infrastructure\SystemContent\Model\SYS_CompanyLegal;

final class EloquentCompanyLegalRepository implements CompanyLegalRepository
{
    public function first(): ?CompanyLegalEntity
    {
        $legal = SYS_CompanyLegal::query()->first();

        if ($legal === null) {
            return null;
        }

        return new CompanyLegalEntity(
            id: (int) $legal->id,
            companyId: (int) $legal->company_id,
            fullName: $legal->full_name,
            shortName: $legal->short_name,
            legalForm: $legal->legal_form,
            legalEmail: $legal->legal_email,
            contractsEmail: $legal->contracts_email,
            legalPhone: $legal->legal_phone,
            owner: $legal->owner,
            responsiblePerson: $legal->responsible_person,
            responsiblePosition: $legal->responsible_position,
            inn: $legal->inn,
            ogrn: $legal->ogrn,
            ogrnip: $legal->ogrnip,
            okpo: $legal->okpo,
            kpp: $legal->kpp,
            taxSystem: $legal->tax_system,
            isVatPayer: (bool) $legal->is_vat_payer,
            vatRateDefault: $legal->vat_rate_default !== null ? (int) $legal->vat_rate_default : null,
            registrationAddress: $legal->registration_address,
            actualAddress: $legal->actual_address,
            postalAddress: $legal->postal_address,
            bankName: $legal->bank_name,
            bik: $legal->bik,
            checkingAccount: $legal->checking_account,
            correspondentAccount: $legal->correspondent_account,
        );
    }
}

