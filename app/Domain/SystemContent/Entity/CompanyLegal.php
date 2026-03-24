<?php

namespace App\Domain\SystemContent\Entity;

final class CompanyLegal
{
    public function __construct(
        private readonly int $id,
        private readonly int $companyId,
        private readonly ?string $legalForm,
        private readonly ?string $legalEmail,
        private readonly ?string $owner,
        private readonly ?string $inn,
        private readonly ?string $ogrn,
        private readonly ?string $okpo,
        private readonly ?string $kpp,
        private readonly ?string $registrationAddress,
    ) {
    }

    public function id(): int { return $this->id; }
    public function companyId(): int { return $this->companyId; }
    public function legalForm(): ?string { return $this->legalForm; }
    public function legalEmail(): ?string { return $this->legalEmail; }
    public function owner(): ?string { return $this->owner; }
    public function inn(): ?string { return $this->inn; }
    public function ogrn(): ?string { return $this->ogrn; }
    public function okpo(): ?string { return $this->okpo; }
    public function kpp(): ?string { return $this->kpp; }
    public function registrationAddress(): ?string { return $this->registrationAddress; }
}

