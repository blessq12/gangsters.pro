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
            legalForm: $legal->legal_form,
            legalEmail: $legal->legal_email,
            owner: $legal->owner,
            inn: $legal->inn,
            ogrn: $legal->ogrn,
            okpo: $legal->okpo,
            kpp: $legal->kpp,
            registrationAddress: $legal->registration_address,
        );
    }
}

