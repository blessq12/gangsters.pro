<?php

namespace App\Domain\Content\Entity;

/**
 * Юридическая информация и реквизиты компании.
 */
final class CompanyLegalInfo
{
    public function __construct(
        private readonly int $id,
        private readonly int $companyId,
        private readonly ?string $fullName,
        private readonly ?string $shortName,
        private readonly ?string $legalForm,
        private readonly ?string $legalEmail,
        private readonly ?string $contractsEmail,
        private readonly ?string $legalPhone,
        private readonly ?string $owner,
        private readonly ?string $responsiblePerson,
        private readonly ?string $responsiblePosition,
        private readonly ?string $inn,
        private readonly ?string $ogrn,
        private readonly ?string $ogrnip,
        private readonly ?string $okpo,
        private readonly ?string $kpp,
        private readonly ?string $taxSystem,
        private readonly bool $isVatPayer,
        private readonly int $vatRateDefault,
        private readonly ?string $registrationAddress,
        private readonly ?string $actualAddress,
        private readonly ?string $postalAddress,
        private readonly ?string $bankName,
        private readonly ?string $bik,
        private readonly ?string $checkingAccount,
        private readonly ?string $correspondentAccount,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function companyId(): int
    {
        return $this->companyId;
    }

    public function fullName(): ?string
    {
        return $this->fullName;
    }

    public function shortName(): ?string
    {
        return $this->shortName;
    }

    public function legalForm(): ?string
    {
        return $this->legalForm;
    }

    public function legalEmail(): ?string
    {
        return $this->legalEmail;
    }

    public function contractsEmail(): ?string
    {
        return $this->contractsEmail;
    }

    public function legalPhone(): ?string
    {
        return $this->legalPhone;
    }

    public function owner(): ?string
    {
        return $this->owner;
    }

    public function responsiblePerson(): ?string
    {
        return $this->responsiblePerson;
    }

    public function responsiblePosition(): ?string
    {
        return $this->responsiblePosition;
    }

    public function inn(): ?string
    {
        return $this->inn;
    }

    public function ogrn(): ?string
    {
        return $this->ogrn;
    }

    public function ogrnip(): ?string
    {
        return $this->ogrnip;
    }

    public function okpo(): ?string
    {
        return $this->okpo;
    }

    public function kpp(): ?string
    {
        return $this->kpp;
    }

    public function taxSystem(): ?string
    {
        return $this->taxSystem;
    }

    public function isVatPayer(): bool
    {
        return $this->isVatPayer;
    }

    public function vatRateDefault(): int
    {
        return $this->vatRateDefault;
    }

    public function registrationAddress(): ?string
    {
        return $this->registrationAddress;
    }

    public function actualAddress(): ?string
    {
        return $this->actualAddress;
    }

    public function postalAddress(): ?string
    {
        return $this->postalAddress;
    }

    public function bankName(): ?string
    {
        return $this->bankName;
    }

    public function bik(): ?string
    {
        return $this->bik;
    }

    public function checkingAccount(): ?string
    {
        return $this->checkingAccount;
    }

    public function correspondentAccount(): ?string
    {
        return $this->correspondentAccount;
    }
}
