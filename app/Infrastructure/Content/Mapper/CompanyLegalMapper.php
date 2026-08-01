<?php

namespace App\Infrastructure\Content\Mapper;

use App\Domain\Content\Entity\CompanyLegalInfo;
use App\Infrastructure\Content\Model\CMP_CompanyLegal;

final class CompanyLegalMapper
{
    public function toDomain(CMP_CompanyLegal $row): CompanyLegalInfo
    {
        return new CompanyLegalInfo(
            id: (int) $row->id,
            companyId: (int) $row->company_id,
            fullName: $this->nullableString($row->full_name),
            shortName: $this->nullableString($row->short_name),
            legalForm: $this->nullableString($row->legal_form),
            legalEmail: $this->nullableString($row->legal_email),
            contractsEmail: $this->nullableString($row->contracts_email),
            legalPhone: $this->nullableString($row->legal_phone),
            owner: $this->nullableString($row->owner),
            responsiblePerson: $this->nullableString($row->responsible_person),
            responsiblePosition: $this->nullableString($row->responsible_position),
            inn: $this->nullableString($row->inn),
            ogrn: $this->nullableString($row->ogrn),
            ogrnip: $this->nullableString($row->ogrnip),
            okpo: $this->nullableString($row->okpo),
            kpp: $this->nullableString($row->kpp),
            taxSystem: $this->nullableString($row->tax_system),
            isVatPayer: (bool) $row->is_vat_payer,
            vatRateDefault: (int) ($row->vat_rate_default ?? 0),
            registrationAddress: $this->nullableString($row->registration_address),
            actualAddress: $this->nullableString($row->actual_address),
            postalAddress: $this->nullableString($row->postal_address),
            bankName: $this->nullableString($row->bank_name),
            bik: $this->nullableString($row->bik),
            checkingAccount: $this->nullableString($row->checking_account),
            correspondentAccount: $this->nullableString($row->correspondent_account),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
