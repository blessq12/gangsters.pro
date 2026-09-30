<?php

namespace App\Domain\Content\Entity;

/**
 * Юридическая информация компании (публичный минимум).
 */
final class CompanyLegalInfo
{
    public function __construct(
        private readonly int $id,
        private readonly int $companyId,
        private readonly ?string $fullName,
        private readonly ?string $inn,
        private readonly ?string $ogrn,
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

    public function inn(): ?string
    {
        return $this->inn;
    }

    public function ogrn(): ?string
    {
        return $this->ogrn;
    }
}
