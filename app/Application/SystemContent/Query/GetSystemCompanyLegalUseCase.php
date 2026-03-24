<?php

namespace App\Application\SystemContent\Query;

use App\Domain\SystemContent\Repository\CompanyLegalRepository;

final class GetSystemCompanyLegalUseCase
{
    public function __construct(
        private readonly CompanyLegalRepository $companyLegals,
    ) {
    }

    public function execute(): array
    {
        $legal = $this->companyLegals->first();

        if ($legal === null) {
            return ['data' => null];
        }

        return [
            'data' => [
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
            ],
        ];
    }
}

