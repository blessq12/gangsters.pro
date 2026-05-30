<?php

namespace App\Domain\SystemContent\Entity;

final class CompanyLegal
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
        private readonly ?int $vatRateDefault,
        private readonly ?string $registrationAddress,
        private readonly ?string $actualAddress,
        private readonly ?string $postalAddress,
        private readonly ?string $bankName,
        private readonly ?string $bik,
        private readonly ?string $checkingAccount,
        private readonly ?string $correspondentAccount,
    ) {
    }

    public function id(): int { return $this->id; }
    public function companyId(): int { return $this->companyId; }
    public function fullName(): ?string { return $this->fullName; }
    public function shortName(): ?string { return $this->shortName; }
    public function legalForm(): ?string { return $this->legalForm; }
    public function legalEmail(): ?string { return $this->legalEmail; }
    public function contractsEmail(): ?string { return $this->contractsEmail; }
    public function legalPhone(): ?string { return $this->legalPhone; }
    public function owner(): ?string { return $this->owner; }
    public function responsiblePerson(): ?string { return $this->responsiblePerson; }
    public function responsiblePosition(): ?string { return $this->responsiblePosition; }
    public function inn(): ?string { return $this->inn; }
    public function ogrn(): ?string { return $this->ogrn; }
    public function ogrnip(): ?string { return $this->ogrnip; }
    public function okpo(): ?string { return $this->okpo; }
    public function kpp(): ?string { return $this->kpp; }
    public function taxSystem(): ?string { return $this->taxSystem; }
    public function isVatPayer(): bool { return $this->isVatPayer; }
    public function vatRateDefault(): ?int { return $this->vatRateDefault; }
    public function registrationAddress(): ?string { return $this->registrationAddress; }
    public function actualAddress(): ?string { return $this->actualAddress; }
    public function postalAddress(): ?string { return $this->postalAddress; }
    public function bankName(): ?string { return $this->bankName; }
    public function bik(): ?string { return $this->bik; }
    public function checkingAccount(): ?string { return $this->checkingAccount; }
    public function correspondentAccount(): ?string { return $this->correspondentAccount; }

    public function withAttributes(
        ?string $fullName = null,
        ?string $shortName = null,
        ?string $legalForm = null,
        ?string $legalEmail = null,
        ?string $contractsEmail = null,
        ?string $legalPhone = null,
        ?string $owner = null,
        ?string $responsiblePerson = null,
        ?string $responsiblePosition = null,
        ?string $inn = null,
        ?string $ogrn = null,
        ?string $ogrnip = null,
        ?string $okpo = null,
        ?string $kpp = null,
        ?string $taxSystem = null,
        ?bool $isVatPayer = null,
        ?int $vatRateDefault = null,
        ?string $registrationAddress = null,
        ?string $actualAddress = null,
        ?string $postalAddress = null,
        ?string $bankName = null,
        ?string $bik = null,
        ?string $checkingAccount = null,
        ?string $correspondentAccount = null,
    ): self {
        return new self(
            id: $this->id,
            companyId: $this->companyId,
            fullName: $fullName ?? $this->fullName,
            shortName: $shortName ?? $this->shortName,
            legalForm: $legalForm ?? $this->legalForm,
            legalEmail: $legalEmail ?? $this->legalEmail,
            contractsEmail: $contractsEmail ?? $this->contractsEmail,
            legalPhone: $legalPhone ?? $this->legalPhone,
            owner: $owner ?? $this->owner,
            responsiblePerson: $responsiblePerson ?? $this->responsiblePerson,
            responsiblePosition: $responsiblePosition ?? $this->responsiblePosition,
            inn: $inn ?? $this->inn,
            ogrn: $ogrn ?? $this->ogrn,
            ogrnip: $ogrnip ?? $this->ogrnip,
            okpo: $okpo ?? $this->okpo,
            kpp: $kpp ?? $this->kpp,
            taxSystem: $taxSystem ?? $this->taxSystem,
            isVatPayer: $isVatPayer ?? $this->isVatPayer,
            vatRateDefault: $vatRateDefault ?? $this->vatRateDefault,
            registrationAddress: $registrationAddress ?? $this->registrationAddress,
            actualAddress: $actualAddress ?? $this->actualAddress,
            postalAddress: $postalAddress ?? $this->postalAddress,
            bankName: $bankName ?? $this->bankName,
            bik: $bik ?? $this->bik,
            checkingAccount: $checkingAccount ?? $this->checkingAccount,
            correspondentAccount: $correspondentAccount ?? $this->correspondentAccount,
        );
    }
}

