<?php

namespace App\Filament\Company\Support;

use App\Application\Company\Legal\DTO\UpdateCompanyLegalDto;

final class FilamentCompanyLegalFormMapper
{
    /**
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function toFormState(array $detail): array
    {
        return $detail;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toDto(array $data): UpdateCompanyLegalDto
    {
        return new UpdateCompanyLegalDto(
            fullName: $data['full_name'] ?? null,
            shortName: $data['short_name'] ?? null,
            legalForm: $data['legal_form'] ?? null,
            legalEmail: $data['legal_email'] ?? null,
            contractsEmail: $data['contracts_email'] ?? null,
            legalPhone: $data['legal_phone'] ?? null,
            owner: $data['owner'] ?? null,
            responsiblePerson: $data['responsible_person'] ?? null,
            responsiblePosition: $data['responsible_position'] ?? null,
            inn: $data['inn'] ?? null,
            ogrn: $data['ogrn'] ?? null,
            ogrnip: $data['ogrnip'] ?? null,
            okpo: $data['okpo'] ?? null,
            kpp: $data['kpp'] ?? null,
            taxSystem: $data['tax_system'] ?? null,
            isVatPayer: (bool) ($data['is_vat_payer'] ?? false),
            vatRateDefault: isset($data['vat_rate_default']) ? (int) $data['vat_rate_default'] : null,
            registrationAddress: $data['registration_address'] ?? null,
            actualAddress: $data['actual_address'] ?? null,
            postalAddress: $data['postal_address'] ?? null,
            bankName: $data['bank_name'] ?? null,
            bik: $data['bik'] ?? null,
            checkingAccount: $data['checking_account'] ?? null,
            correspondentAccount: $data['correspondent_account'] ?? null,
        );
    }
}
