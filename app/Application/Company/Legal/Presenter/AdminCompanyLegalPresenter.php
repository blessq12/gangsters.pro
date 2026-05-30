<?php

namespace App\Application\Company\Legal\Presenter;

use App\Domain\SystemContent\Entity\CompanyLegal;

final class AdminCompanyLegalPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function present(?CompanyLegal $legal, int $companyId): array
    {
        if ($legal === null) {
            return [
                'id' => 0,
                'company_id' => $companyId,
                'full_name' => null,
                'short_name' => null,
                'legal_form' => null,
                'legal_email' => null,
                'contracts_email' => null,
                'legal_phone' => null,
                'owner' => null,
                'responsible_person' => null,
                'responsible_position' => null,
                'inn' => null,
                'ogrn' => null,
                'ogrnip' => null,
                'okpo' => null,
                'kpp' => null,
                'tax_system' => null,
                'is_vat_payer' => false,
                'vat_rate_default' => null,
                'registration_address' => null,
                'actual_address' => null,
                'postal_address' => null,
                'bank_name' => null,
                'bik' => null,
                'checking_account' => null,
                'correspondent_account' => null,
            ];
        }

        return [
            'id' => $legal->id(),
            'company_id' => $legal->companyId(),
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
        ];
    }
}
