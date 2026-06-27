<?php

namespace App\Application\Company\useCases;

use App\Domain\Client\ValueObject\PhoneNumber;
use App\Domain\Company\Entity\CompanyLegalInfo;
use App\Domain\Company\Repository\CompanyLegalRepository;

/**
 * Сценарий: получить юридическую информацию компании.
 */
final class GetCompanyLegalDataUseCase
{
    public function __construct(
        private readonly CompanyLegalRepository $legal,
    ) {}

    /**
     * @return array{data: array<string, mixed>|null}
     */
    public function execute(): array
    {
        $legal = $this->legal->findPublic();

        return [
            'data' => $legal instanceof CompanyLegalInfo
                ? $this->mapLegal($legal)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapLegal(CompanyLegalInfo $legal): array
    {
        return [
            'id' => $legal->id(),
            'company_id' => $legal->companyId(),
            'full_name' => $legal->fullName(),
            'short_name' => $legal->shortName(),
            'legal_form' => $legal->legalForm(),
            'legal_email' => $legal->legalEmail(),
            'contracts_email' => $legal->contractsEmail(),
            'legal_phone' => self::formatOptionalPhone($legal->legalPhone()),
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

    private static function formatOptionalPhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        return PhoneNumber::tryFormatFromRaw($phone) ?? trim($phone);
    }
}
