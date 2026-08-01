<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\Company;

interface CompanyRepository
{
    public const SINGLETON_ID = 1;

    public function findPublic(): ?Company;
}
