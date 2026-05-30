<?php

namespace App\Infrastructure\SystemContent\Repository;

use App\Domain\SystemContent\Entity\CompanyLegal as CompanyLegalEntity;
use App\Domain\SystemContent\Repository\CompanyLegalRepository;
use App\Infrastructure\SystemContent\Model\SYS_Company;
use App\Infrastructure\SystemContent\Model\SYS_CompanyLegal;

final class EloquentCompanyLegalRepository implements CompanyLegalRepository
{
    public function first(): ?CompanyLegalEntity
    {
        $legal = SYS_CompanyLegal::query()->first();

        if ($legal === null) {
            return null;
        }

        return $this->toEntity($legal);
    }

    public function save(CompanyLegalEntity $legal): CompanyLegalEntity
    {
        if ($legal->id() > 0) {
            $model = SYS_CompanyLegal::query()->findOrFail($legal->id());
        } else {
            $model = new SYS_CompanyLegal();
            $model->company_id = $legal->companyId() > 0
                ? $legal->companyId()
                : (int) SYS_Company::query()->value('id');
        }

        $model->fill([
            'full_name' => $legal->fullName(),
            'short_name' => $legal->shortName(),
            'legal_form' => $legal->legalForm(),
            'legal_email' => $legal->legalEmail(),
            'contracts_email' => $legal->contractsEmail(),
            'legal_phone' => $legal->legalPhone(),
            'owner' => $legal->owner(),
            'responsible_person' => $legal->responsiblePerson(),
            'responsible_position' => $legal->responsiblePosition(),
            'inn' => $legal->inn(),
            'ogrn' => $legal->ogrn(),
            'ogrnip' => $legal->ogrnip(),
            'okpo' => $legal->okpo(),
            'kpp' => $legal->kpp(),
            'tax_system' => $legal->taxSystem(),
            'is_vat_payer' => $legal->isVatPayer(),
            'vat_rate_default' => $legal->vatRateDefault(),
            'registration_address' => $legal->registrationAddress(),
            'actual_address' => $legal->actualAddress(),
            'postal_address' => $legal->postalAddress(),
            'bank_name' => $legal->bankName(),
            'bik' => $legal->bik(),
            'checking_account' => $legal->checkingAccount(),
            'correspondent_account' => $legal->correspondentAccount(),
        ]);

        $model->save();

        return $this->toEntity($model);
    }

    private function toEntity(SYS_CompanyLegal $legal): CompanyLegalEntity
    {
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
