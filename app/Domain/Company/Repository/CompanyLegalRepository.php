<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\CompanyLegalInfo;

interface CompanyLegalRepository
{
    public function findPublic(): ?CompanyLegalInfo;
}
