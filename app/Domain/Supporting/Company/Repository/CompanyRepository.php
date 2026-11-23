<?php

namespace App\Domain\Supporting\Company\Repository;

use App\Domain\Supporting\Company\Entity\Company;

interface CompanyRepository
{
    public const SINGLETON_ID = 1;

    public function findPublic(): ?Company;

    public function save(Company $company): void;
}
