<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\Company;

interface CompanyRepository
{
    public const SINGLETON_ID = 1;

    public function findPublic(): ?Company;
}
