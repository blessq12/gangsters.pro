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
                'legal_form' => $legal->legalForm(),
                'legal_email' => $legal->legalEmail(),
                'owner' => $legal->owner(),
                'inn' => $legal->inn(),
                'ogrn' => $legal->ogrn(),
                'okpo' => $legal->okpo(),
                'kpp' => $legal->kpp(),
                'registration_address' => $legal->registrationAddress(),
            ],
        ];
    }
}

