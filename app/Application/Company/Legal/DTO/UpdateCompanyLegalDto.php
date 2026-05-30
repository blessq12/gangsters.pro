<?php

namespace App\Application\Company\Legal\DTO;

final readonly class UpdateCompanyLegalDto
{
    public function __construct(
        public ?string $fullName,
        public ?string $shortName,
        public ?string $legalForm,
        public ?string $legalEmail,
        public ?string $contractsEmail,
        public ?string $legalPhone,
        public ?string $owner,
        public ?string $responsiblePerson,
        public ?string $responsiblePosition,
        public ?string $inn,
        public ?string $ogrn,
        public ?string $ogrnip,
        public ?string $okpo,
        public ?string $kpp,
        public ?string $taxSystem,
        public bool $isVatPayer,
        public ?int $vatRateDefault,
        public ?string $registrationAddress,
        public ?string $actualAddress,
        public ?string $postalAddress,
        public ?string $bankName,
        public ?string $bik,
        public ?string $checkingAccount,
        public ?string $correspondentAccount,
    ) {
    }
}
